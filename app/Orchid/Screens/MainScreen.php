<?php

namespace App\Orchid\Screens;

use App\Models\Group;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class MainScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $user = Auth::user();
        if ($user) {
            $user->load(['department', 'degree', 'roles']);
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
        $selectedSubjects = collect();
        $maxSubjectsLimit = 0;
        $selectionProgressPercent = 0;

        if ($user) {
            $specialtyId = request()->cookie('user_specialty_id');
            if ($specialtyId) {
                $studentSpecialty = UserSpecialty::with(['group.semesterLimits', 'subjects'])->find($specialtyId);
            }
            if (!$studentSpecialty && $user->specialties()->exists()) {
                $studentSpecialty = $user->specialties()->with(['group.semesterLimits', 'subjects'])->first();
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

        $isStudent = !$isStaff || ($studentSpecialty !== null);

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

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
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
}

