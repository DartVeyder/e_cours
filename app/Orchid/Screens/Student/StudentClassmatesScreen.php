<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Student;

use App\Models\UserSpecialty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class StudentClassmatesScreen extends Screen
{
    public ?string $groupName = null;

    /**
     * Determine whether the user has staff privileges.
     */
    public function isStaffUser(?\App\Models\User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }

        return $user->hasAccess('platform.systems.roles') ||
            $user->hasAccess('platform.systems.users') ||
            $user->hasAccess('platform.systems.students') ||
            $user->hasAccess('platform.systems.subjects') ||
            $user->hasAccess('dekanat') ||
            $user->roles->contains('slug', 'administrator') ||
            $user->roles->contains('slug', 'admin') ||
            $user->roles->contains('slug', 'dekanat');
    }

    /**
     * Resolve active specialty ID from cookie or fallback to user's first specialty.
     */
    public function resolveSpecialtyId(): ?int
    {
        $specialtyId = request()->cookie('user_specialty_id');
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $isStaff = $this->isStaffUser($user);

        if ($specialtyId) {
            $query = UserSpecialty::where('id', $specialtyId);
            if (!$isStaff) {
                $query->where('user_id', $user->id);
            }
            if ($query->exists()) {
                return (int) $specialtyId;
            }
        }

        $user->loadMissing('specialties');
        $specialties = $user->specialties;

        if ($specialties->count() === 1) {
            $autoId = (int) $specialties->first()->id;
            Cookie::queue('user_specialty_id', $autoId, 1440);
            return $autoId;
        }

        if ($specialties->isNotEmpty()) {
            return (int) $specialties->first()->id;
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
        $studentSpecialty = null;
        $classmates = collect();

        if ($user) {
            $specialtyId = $this->resolveSpecialtyId();
            if ($specialtyId) {
                $studentSpecialty = UserSpecialty::find($specialtyId);
            }

            if ($studentSpecialty && ($studentSpecialty->group_id || $studentSpecialty->group_name)) {
                $this->groupName = $studentSpecialty->group_name;

                $classmatesQuery = UserSpecialty::query();
                if ($studentSpecialty->group_id) {
                    $classmatesQuery->where('group_id', $studentSpecialty->group_id);
                } else {
                    $classmatesQuery->where('group_name', $studentSpecialty->group_name);
                }

                $classmates = $classmatesQuery
                    ->with(['subjects'])
                    ->orderBy('full_name')
                    ->get([
                        'id',
                        'user_id',
                        'full_name',
                        'email',
                        'specialty',
                        'education_program',
                        'study_form',
                        'degree',
                        'department',
                        'group_name'
                    ]);
            }
        }

        return [
            'user' => $user,
            'studentSpecialty' => $studentSpecialty,
            'groupName' => $this->groupName,
            'classmates' => $classmates,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->groupName 
            ? "Академічна група: {$this->groupName}" 
            : 'Мої одногрупники';
    }

    /**
     * The description displayed on the user's screen under the heading.
     */
    public function description(): ?string
    {
        return 'Список студентів та контактні дані вашої академічної групи';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make('На головну')
                ->icon('bs.house')
                ->route('platform.main'),

            Link::make('Вибіркові дисципліни')
                ->icon('bs.book')
                ->route('platform.selsubjects'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('student.classmates'),
        ];
    }
}
