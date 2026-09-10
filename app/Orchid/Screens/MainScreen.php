<?php

namespace App\Orchid\Screens;

use App\Models\Group;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class MainScreen extends Screen
{
    /**
     * Resolve active specialty ID from cookie or auto-select if user has only 1 specialty.
     *
     * @return int|null
     */
    public function resolveSpecialtyId(): ?int
    {
        $specialtyId = request()->cookie('user_specialty_id');
        $user = Auth::user();

        if ($specialtyId && UserSpecialty::where('id', $specialtyId)->exists()) {
            return (int) $specialtyId;
        }

        if ($user) {
            $user->loadMissing('specialties');
            $specialties = $user->specialties;

            if ($specialties->count() === 1) {
                $autoId = (int) $specialties->first()->id;
                Cookie::queue('user_specialty_id', $autoId, 1440);
                return $autoId;
            }
        }

        return null;
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = Auth::user();
        if ($user) {
            $user->load(['department', 'degree', 'roles', 'specialties']);
        }

        $isAdmin = $user && (
            $user->hasAccess('platform.systems.roles') ||
            $user->hasAccess('platform.systems.users') ||
            $user->roles->contains('slug', 'administrator') ||
            $user->roles->contains('slug', 'admin')
        );

        $isDekanat = $user && (
            $user->roles->contains('slug', 'dekanat') ||
            $user->hasAccess('platform.systems.students') ||
            $user->hasAccess('platform.systems.subjects')
        );

        $isStaff = $isAdmin || $isDekanat;

        $isSelectionEnabled = Setting::where('key', 'subject_selection_enabled')->value('value') !== '0';
        $isTestMode = (bool) Setting::where('key', 'google_sheets_test_mode')->value('value');

        // Global stats (for Admin / Dekanat)
        $totalStudents = 0;
        $totalSubjects = 0;
        $totalGroups = 0;
        $totalSelections = 0;
        $studentsWithSelectionsCount = 0;
        $recentActivities = collect();

        if ($isStaff) {
            $totalStudents = UserSpecialty::count();
            $totalSubjects = Subject::where('active', 1)->count();
            $totalGroups = Group::count();
            $totalSelections = UserSpecialtySubject::count();
            $studentsWithSelectionsCount = UserSpecialtySubject::distinct('user_specialty_id')->count('user_specialty_id');
            
            try {
                $recentActivities = \Spatie\Activitylog\Models\Activity::with('causer')->latest()->take(6)->get();
            } catch (\Throwable $e) {
                $recentActivities = collect();
            }
        }

        // Student-specific context
        $studentSpecialty = null;
        $userSpecialties = $user ? $user->specialties : collect();
        $selectedSubjects = collect();
        $maxSubjectsLimit = 0;
        $selectionProgressPercent = 0;

        if ($user) {
            $specialtyId = $this->resolveSpecialtyId();

            if ($specialtyId) {
                $studentSpecialty = UserSpecialty::with(['group.semesterLimits', 'subjects'])->find($specialtyId);
            }

            if ($studentSpecialty) {
                $selectedSubjects = $studentSpecialty->subjects;
                if ($studentSpecialty->group && $studentSpecialty->group->semesterLimits) {
                    $maxSubjectsLimit = (int) $studentSpecialty->group->semesterLimits->sum('max_subjects');
                }
                if ($maxSubjectsLimit > 0) {
                    $selectionProgressPercent = min(100, (int) round(($selectedSubjects->count() / $maxSubjectsLimit) * 100));
                }
            }
        }

        $isStudent = !$isStaff || ($studentSpecialty !== null) || ($userSpecialties->isNotEmpty());

        return [
            'user' => $user,
            'isAdmin' => $isAdmin,
            'isDekanat' => $isDekanat,
            'isStaff' => $isStaff,
            'isStudent' => $isStudent,
            'isSelectionEnabled' => $isSelectionEnabled,
            'isTestMode' => $isTestMode,
            'totalStudents' => $totalStudents,
            'totalSubjects' => $totalSubjects,
            'totalGroups' => $totalGroups,
            'totalSelections' => $totalSelections,
            'studentsWithSelectionsCount' => $studentsWithSelectionsCount,
            'recentActivities' => $recentActivities,
            'studentSpecialty' => $studentSpecialty,
            'userSpecialties' => $userSpecialties,
            'hasSpecialtySelected' => ($studentSpecialty !== null),
            'selectedSubjects' => $selectedSubjects,
            'maxSubjectsLimit' => $maxSubjectsLimit,
            'selectionProgressPercent' => $selectionProgressPercent,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Головна панель';
    }

    /**
     * The description is displayed on the user's screen under the heading.
     */
    public function description(): ?string
    {
        return 'Огляд системи та статус вибору вибіркових дисциплін';
    }

    private function specialtiesButtons()
    {
        $user = Auth::user();
        if (!$user) {
            return Button::make('Виберіть спеціальність')->disabled();
        }

        $specialties = $user->loadMissing('specialties')->specialties;
        if ($specialties->isEmpty()) {
            return null;
        }

        $userSpecialtyId = $this->resolveSpecialtyId();

        if (!$userSpecialtyId) {
            $titleButtons = '⚠️ Виберіть спеціальність';
        } else {
            $userSpecialty = UserSpecialty::with('group')->find($userSpecialtyId);
            if ($userSpecialty) {
                $groupName = $userSpecialty->group_name ?? ($userSpecialty->group?->name ?? 'Без групи');
                $semesterCount = $userSpecialty->group?->semester_count ?? 0;
                $titleButtons = "🎓 {$userSpecialty->specialty} ({$groupName}, {$userSpecialty->degree}, {$userSpecialty->full_name}, Семестрів: {$semesterCount})";
            } else {
                $titleButtons = '⚠️ Виберіть спеціальність';
            }
        }

        $array = [];
        foreach ($specialties as $specialty) {
            $isCurrent = ($specialty->id == $userSpecialtyId);
            $prefix = $isCurrent ? '✓ ' : '';
            $groupName = $specialty->group_name ?? ($specialty->group?->name ?? 'Без групи');
            $label = $prefix . $specialty->specialty . " ({$groupName})";
            $array[] = Button::make($label)
                ->method('chooseSpecialty', [
                    'id' => $specialty->id,
                    'text' => $specialty->specialty . " ({$groupName})",
                ]);
        }

        return DropDown::make($titleButtons)->list($array);
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        $buttons = [];
        $specialtiesDropdown = $this->specialtiesButtons();
        if ($specialtiesDropdown) {
            $buttons[] = $specialtiesDropdown;
        }

        return $buttons;
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('main')
        ];
    }

    public function chooseSpecialty($id = null, $text = null)
    {
        $id = $id ?? request('id');
        $text = $text ?? request('text');

        if ($id) {
            Cookie::queue('user_specialty_id', $id, 1440);
        }
        Toast::info("Вибрано: " . $text);

        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'specialty_name' => $text
            ])
            ->log("Вибір спеціальності на головній: {$text}");
    }
}

