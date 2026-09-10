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
                    $degreeId = null;
                    if (!empty($row['degree'])) {
                        $degree = \App\Models\Degree::where('name', $row['degree'])->first();
                        $degreeId = $degree ? $degree->id : null;
                    }

                    $departmentId = null;
                    if (!empty($row['department'])) {
                        $department = \App\Models\Department::where('name', $row['department'])->first();
                        $departmentId = $department ? $department->id : null;
                    }

                    $groupId = null;
                    $groupName = trim($row['group_name'] ?? '');
                    if ($groupName !== '' && $groupName !== '?' && $groupName !== '-') {
                        $group = \App\Models\Group::firstOrCreate(
                            ['name' => $groupName],
                            [
                                'department_id' => $departmentId,
                                'degree_id' => $degreeId,
                            ]
                        );
                        $groupId = $group->id;
                    } else {
                        $groupName = null;
                    }

                    $data = [
                        'user_id' => $user->id,
                        'department_id' => $departmentId,
                        'degree_id' => $degreeId,
                        'group_id' => $groupId,
                        'email' => $row['email'] ?? null,
                        'card_id' => $row['card_id'] ?? null,
                        'status_from' => $row['status_from'] ?? null,
                        'study_status' => $row['study_status'] ?? null,
                        'fo_id' => $row['fo_id'] ?? null,
                        'full_name' => $row['full_name'] ?? null,
                        'birth_date' => $row['birth_date'] ?? null,
                        'dpo_type' => $row['dpo_type'] ?? null,
                        'document_series' => $row['document_series'] ?? null,
                        'document_number' => $row['document_number'] ?? null,
                        'issue_date' => $row['issue_date'] ?? null,
                        'valid_until' => $row['valid_until'] ?? null,
                        'gender' => $row['gender'] ?? null,
                        'citizenship' => $row['citizenship'] ?? null,
                        'name_en' => $row['name_en'] ?? null,
                        'rnokpp' => $row['rnokpp'] ?? null,
                        'valid_rnokpp' => $row['valid_rnokpp'] ?? null,
                        'license_year' => $row['license_year'] ?? null,
                        'study_start' => $row['study_start'] ?? null,
                        'study_end' => $row['study_end'] ?? null,
                        'next_level_admission_date' => $row['next_level_admission_date'] ?? null,
                        'department' => $row['department'] ?? null,
                        'dual_form' => $row['dual_form'] ?? null,
                        'degree' => $row['degree'] ?? null,
                        'admission_basis' => $row['admission_basis'] ?? null,
                        'study_form' => $row['study_form'] ?? null,
                        'funding_source' => $row['funding_source'] ?? null,
                        'other_specialty' => $row['other_specialty'] ?? null,
                        'shortened_term' => $row['shortened_term'] ?? null,
                        'specialty' => $row['specialty'] ?? null,
                        'specialization' => $row['specialization'] ?? null,
                        'op_id' => $row['op_id'] ?? null,
                        'education_program' => $row['education_program'] ?? null,
                        'profession' => $row['profession'] ?? null,
                        'course' => $row['course'] ?? null,
                        'group_name' => $groupName,
                        'foreigner_type' => $row['foreigner_type'] ?? null,
                        'category_code' => $row['category_code'] ?? null,
                        'has_education_doc' => $row['has_education_doc'] ?? null,
                        'has_student_card' => $row['has_student_card'] ?? null,
                        'has_academic_reference' => $row['has_academic_reference'] ?? null,
                        'expulsion_reason' => $row['expulsion_reason'] ?? null,
                        'academic_leave_reason' => $row['academic_leave_reason'] ?? null,
                        'status_to' => $row['status_to'] ?? null,
                        'diploma_status' => $row['diploma_status'] ?? null,
                        'student_card_status' => $row['student_card_status'] ?? null,
                        'qualification_certificate_status' => $row['qualification_certificate_status'] ?? null,
                        'budget_year' => $row['budget_year'] ?? null,
                        'regional_order' => $row['regional_order'] ?? null,
                        'enrollment_order' => $row['enrollment_order'] ?? null,
                        'previous_institution' => $row['previous_institution'] ?? null,
                        'previous_education_doc' => $row['previous_education_doc'] ?? null,
                        'previous_study_info' => $row['previous_study_info'] ?? null,
                        'has_academic_reference_doc' => $row['has_academic_reference_doc'] ?? null,
                        'has_expulsion_reference' => $row['has_expulsion_reference'] ?? null,
                        'has_student_ticket' => $row['has_student_ticket'] ?? null,
                        'has_diploma' => $row['has_diploma'] ?? null,
                        'enrollment_info' => $row['enrollment_info'] ?? null,
                        'kb_entry' => $row['kb_entry'] ?? null,
                        'kr_without_pzso' => $row['kr_without_pzso'] ?? null,
                        'last_update' => $row['last_update'] ?? null,
                        'budget_transfer_category_code' => $row['budget_transfer_category_code'] ?? null,
                        'budget_transfer_category_name' => $row['budget_transfer_category_name'] ?? null,
                        'card_creation_method' => $row['card_creation_method'] ?? null,
                        'dissertation_defense_renewal' => $row['dissertation_defense_renewal'] ?? null,
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
        $specialties = Auth::user()->load('specialties')->specialties;

        if ($specialties->count() === 1) {
            Cookie::queue('user_specialty_id', $specialties->first()->id, 1440);
        } else {
            Cookie::queue(Cookie::forget('user_specialty_id'));
        }
    }


}
