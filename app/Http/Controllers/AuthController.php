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
            foreach ($students as $row) {
                $existingUser = UserSpecialty::where('card_id', $row['card_id'])->where('user_id', $user->id)->first();

                if (!$existingUser) {
                    $data = [
                        'user_id' => $user->id,
                        'email' => $row['email'],
                        'card_id' => $row['card_id'],
                        'status_from' => $row['status_from'],
                        'study_status' => $row['study_status'],
                        'fo_id' => $row['fo_id'],
                        'full_name' => $row['full_name'],
                        'birth_date' => $row['birth_date'],
                        'dpo_type' => $row['dpo_type'],
                        'document_series' => $row['document_series'],
                        'document_number' => $row['document_number'],
                        'issue_date' => $row['issue_date'],
                        'valid_until' => $row['valid_until'],
                        'gender' => $row['gender'],
                        'citizenship' => $row['citizenship'],
                        'name_en' => $row['name_en'],
                        'rnokpp' => $row['rnokpp'],
                        'valid_rnokpp' => $row['valid_rnokpp'],
                        'license_year' => $row['license_year'],
                        'study_start' => $row['study_start'],
                        'study_end' => $row['study_end'],
                        'next_level_admission_date' => $row['next_level_admission_date'],
                        'department' => $row['department'],
                        'dual_form' => $row['dual_form'],
                        'degree' => $row['degree'],
                        'admission_basis' => $row['admission_basis'],
                        'study_form' => $row['study_form'],
                        'funding_source' => $row['funding_source'],
                        'other_specialty' => $row['other_specialty'],
                        'shortened_term' => $row['shortened_term'],
                        'specialty' => $row['specialty'],
                        'specialization' => $row['specialization'],
                        'op_id' => $row['op_id'],
                        'education_program' => $row['education_program'],
                        'profession' => $row['profession'],
                        'course' => $row['course'],
                        'group' => $row['group'],
                        'foreigner_type' => $row['foreigner_type'],
                        'category_code' => $row['category_code'],
                        'has_education_doc' => $row['has_education_doc'],
                        'has_student_card' => $row['has_student_card'],
                        'has_academic_reference' => $row['has_academic_reference'],
                        'expulsion_reason' => $row['expulsion_reason'],
                        'academic_leave_reason' => $row['academic_leave_reason'],
                        'status_to' => $row['status_to'],
                        'diploma_status' => $row['diploma_status'],
                        'student_card_status' => $row['student_card_status'],
                        'qualification_certificate_status' => $row['qualification_certificate_status'],
                        'budget_year' => $row['budget_year'],
                        'regional_order' => $row['regional_order'],
                        'enrollment_order' => $row['enrollment_order'],
                        'previous_institution' => $row['previous_institution'],
                        'previous_education_doc' => $row['previous_education_doc'],
                        'previous_study_info' => $row['previous_study_info'],
                        'has_academic_reference_doc' => $row['has_academic_reference_doc'],
                        'has_expulsion_reference' => $row['has_expulsion_reference'],
                        'has_student_ticket' => $row['has_student_ticket'],
                        'has_diploma' => $row['has_diploma'],
                        'enrollment_info' => $row['enrollment_info'],
                        'kb_entry' => $row['kb_entry'],
                        'kr_without_pzso' => $row['kr_without_pzso'],
                        'last_update' => $row['last_update'],
                        'budget_transfer_category_code' => $row['budget_transfer_category_code'],
                        'budget_transfer_category_name' => $row['budget_transfer_category_name'],
                        'card_creation_method' => $row['card_creation_method'],
                        'dissertation_defense_renewal' => $row['dissertation_defense_renewal'],
                    ];
                    UserSpecialty::updateOrCreate(
                        ['card_id' => $row['card_id']],
                        $data
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
