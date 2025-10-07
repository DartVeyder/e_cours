<?php

namespace App\Http\Controllers;

use App\Models\UserSpecialty;
use App\Services\GoogleSheet\StudentsSheet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class AuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $socialiteUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/login');
        }

        $user = User::with('roles')->where([
            'email' => $socialiteUser->getEmail()
        ])->first();

        $isAdministrator = $user && ($user->roles->contains('slug', 'administrator') || $user->roles->contains('slug', 'dekanat'));

        if ($isAdministrator) {
            Auth::login($user,true);
            $this->setCookieSpecialtyId();
            $roleNames = $user->roles->pluck('name')->toArray();

            activity()
                ->causedBy($user)
                ->withProperties([
                    'email' => $user->email,
                    'role' => 'administrator',
                    'status' => 'existing'
                ])
                ->log($roleNames[0] . ' увійшов у систему');

            return redirect()->route('platform.main');
        }

        if (!str_ends_with($socialiteUser->getEmail(), '@dspu.edu.ua')) {
            return redirect('/login')->withErrors([
                'email' => 'Увійти можуть лише користувачі з корпоративної електронної адреси dspu.edu.ua.'
            ]);
        }

        $studentsSheet = new StudentsSheet();
        $students = $studentsSheet->getStudentByEmail($socialiteUser->getEmail());

        if (!$students) {
            return redirect('/login')->withErrors([
                'email' => 'Відсутній студент в БД'
            ]);
        }

        $logType = 'existing'; // статус користувача за замовчуванням

        if (!$user) {
            $validator = Validator::make(
                ['email' => $socialiteUser->getEmail()],
                ['email' => ['unique:users,email']],
                ['email.unique' => 'Couldn\'t log in. Maybe you used a different login method?']
            );

            if ($validator->fails()) {
                return redirect('/login');
            }

            $user = new User();
            $user->name = $socialiteUser->getName();
            $user->email = $socialiteUser->getEmail();
            $user->provider = 'google';
            $user->provider_id = $socialiteUser->getId();
            $user->permissions = [
                "platform.index" => true,
                "platform.systems.roles" => false,
                "platform.systems.users" => false,
                "platform.systems.attachment" => false,
            ];
            $user->save();
            $user->replaceRoles([0 => 1]);

            $logType = 'new'; // позначаємо, що користувач новий

            activity()
                ->causedBy($user)
                ->withProperties([
                    'email' => $user->email,
                    'status' => 'new'
                ])
                ->log("Створено нового користувача через Google: {$user->name}");
        }

        if ($user->id) {
            foreach ($students as $student) {
                $existingUser = UserSpecialty::where('card_id', $student['card_id'])->where('user_id', $user->id)->first();

                if (!$existingUser) {
                    $student['user_id'] = $user->id;
                    UserSpecialty::updateOrCreate(
                        ['card_id' => $student['card_id']],
                        $student
                    );

                }
            }
        }

        Auth::login($user,true);
        $this->setCookieSpecialtyId();

        activity()
            ->causedBy($user)
            ->withProperties([
                'email' => $user->email,
                'status' => $logType
            ])
            ->log($logType === 'new'
                ? "Новий користувач увійшов у систему: {$user->name}"
                : "Існуючий користувач увійшов у систему: {$user->name}"
            );

        return redirect()->route('platform.main');
    }

    private function setCookieSpecialtyId()
    {
        $specialty = Auth::user()->load('specialties')->specialties->first();

        if ($specialty) {
            Cookie::queue('user_specialty_id', $specialty->id, 1440);
        } else {
            Cookie::queue(Cookie::forget('user_specialty_id'));
        }
    }


}
