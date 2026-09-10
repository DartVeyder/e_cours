<?php

namespace App\Orchid\Screens\Selsubject;

use App\Models\Subject;
use App\Models\UserSpecialty;
use App\Models\UserSpecialtySubject;
use App\Orchid\Layouts\SelSubject\SelSubjectListLayout;
use App\Services\GoogleSheet\GroupsSheet;
use App\Services\GoogleSheet\SelsubjectSheet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class SelsubjectListScreen extends Screen
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
        $user = Auth::user()->load(['department', 'degree', 'roles', 'specialties']);
        $specialtyId = $this->resolveSpecialtyId();

        $subjectsQuery = Subject::filters()
            ->defaultSort('is_selected', 'DESC')
            ->withCount(['users as is_selected' => function ($query) use ($specialtyId) {
                if ($specialtyId) {
                    $query->where('user_specialty_subjects.user_specialty_id', $specialtyId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }])
            ->addSelect([
                'is_student_choice' => function ($query) use ($specialtyId) {
                    if ($specialtyId) {
                        $query->select('user_specialty_subjects.is_student_choice')
                            ->from('user_specialty_subjects')
                            ->whereColumn('user_specialty_subjects.subject_id', 'subjects.id')
                            ->where('user_specialty_subjects.user_specialty_id', $specialtyId)
                            ->limit(1);
                    } else {
                        $query->selectRaw('NULL');
                    }
                },
                'semester' => function ($query) use ($specialtyId) {
                    if ($specialtyId) {
                        $query->select('user_specialty_subjects.semester')
                            ->from('user_specialty_subjects')
                            ->whereColumn('user_specialty_subjects.subject_id', 'subjects.id')
                            ->where('user_specialty_subjects.user_specialty_id', $specialtyId)
                            ->limit(1);
                    } else {
                        $query->selectRaw('NULL');
                    }
                },
            ])
            ->where(function ($query) use ($specialtyId) {
                $query->where('active', 1);
                if ($specialtyId) {
                    $query->orWhereExists(function ($sub) use ($specialtyId) {
                        $sub->selectRaw(1)
                            ->from('user_specialty_subjects')
                            ->whereColumn('user_specialty_subjects.subject_id', 'subjects.id')
                            ->where('user_specialty_subjects.user_specialty_id', $specialtyId);
                    });
                }
            })
            ->with(['users.specialties.group']);

        // Якщо є активна спеціальність — обмежуємо предмети її рівнем освіти
        if ($specialtyId) {
            $userSpecialty = UserSpecialty::find($specialtyId);

            if ($userSpecialty && $userSpecialty->degree) {
                $subjectsQuery->where('education_level', $userSpecialty->degree);
            }
        } else {
            if ($user && $user->degree) {
                $subjectsQuery->where('education_level', $user->degree->name);
            } elseif ($user && $user->loadMissing('specialties')->specialties->isNotEmpty()) {
                $degrees = $user->specialties->pluck('degree')->filter()->unique();
                if ($degrees->count() === 1) {
                    $subjectsQuery->where('education_level', $degrees->first());
                }
            }
        }

        return [
            'subjects' => $subjectsQuery->paginate(),
        ];
    }


    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Вибіркові освітні компоненти університету';
    }

    public function description(): ?string
    {
        $userSpecialtyId = $this->resolveSpecialtyId();
        $user = Auth::user();

        if (!$userSpecialtyId) {
            if ($user && $user->loadMissing('specialties')->specialties->count() > 1) {
                return "⚠️ Спеціальність не обрана. Оберіть спеціальність у верхньому меню для доступу до вибору дисциплін.";
            }
            if ($user && $user->loadMissing('specialties')->specialties->count() === 0) {
                return "⚠️ Картку спеціальності не знайдено. Зверніться до деканату для внесення даних.";
            }
            return "⚠️ Спеціальність не обрана. Без вибору спеціальності вибір дисциплін неможливий.";
        }

        $userSpecialty = UserSpecialty::with('group.semesterLimits')->find($userSpecialtyId);

        if (!$userSpecialty) {
            return "Спеціальність не знайдено";
        }

        $semesterCounts = UserSpecialtySubject::where('user_specialty_id', $userSpecialtyId)
            ->selectRaw('semester, COUNT(*) as total')
            ->groupBy('semester')
            ->pluck('total', 'semester'); // ключ = семестр, значення = кількість обраних предметів

        $output = [];

        // Проходимо всі семестри групи
        if ($userSpecialty->group && $userSpecialty->group->semesterLimits) {
            foreach ($userSpecialty->group->semesterLimits as $limit) {
                $semester = $limit->semester;
                $selected = $semesterCounts->get($semester, 0); // скільки обрано
                $max = $limit->max_subjects; // ліміт
                $output[] = "Семестр {$semester}: {$selected}/{$max} ";
            }
        }

        $description = count($output) ? implode(', ', $output) : "Ще не вибрано жодного предмету";

        $isSelectionEnabled = \App\Models\Setting::where('key', 'subject_selection_enabled')->value('value') !== '0';
        if (!$isSelectionEnabled && (!$user || (!$user->roles->contains('slug', 'dekanat') && !$user->hasAccess('platform.systems.roles')))) {
            $description .= " | 🔴 Увага! Редагування списку дисциплін наразі закрите адміністрацією.";
        }

        return $description;
    }


    private function specialtiesButtons()
    {
        $array = [];
        $user = Auth::user();
        if (!$user) {
            return Button::make('Виберіть спеціальність')->disabled();
        }

        $specialties = $user->loadMissing('specialties')->specialties;
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

        if (count($specialties) > 0) {
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
            return DropDown::make($titleButtons)
                ->list($array);
        } else {
            return Button::make($titleButtons)->disabled();
        }
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            $this->specialtiesButtons(),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $specialtyId = $this->resolveSpecialtyId();
        $user = Auth::user();
        $userSpecialties = $user ? $user->loadMissing('specialties')->specialties : collect();

        $layouts = [];

        if (!$specialtyId) {
            $layouts[] = Layout::view('partials.no-specialty-alert', [
                'userSpecialties' => $userSpecialties,
            ]);
        }

        $layouts[] = SelSubjectListLayout::class;

        return $layouts;
    }

    public function chooseSubject($subjectId, $subjectName, $semester)
    {
        $isSelectionEnabled = \App\Models\Setting::where('key', 'subject_selection_enabled')->value('value') !== '0';
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$isSelectionEnabled && (!$user || (!$user->roles->contains('slug', 'dekanat') && !$user->hasAccess('platform.systems.roles')))) {
            Toast::error('Вибір дисциплін наразі закритий адміністрацією.');
            return;
        }

        $userSpecialtyId = $this->resolveSpecialtyId();
        $userSpecialty = $userSpecialtyId ? UserSpecialty::find($userSpecialtyId) : null;

        if (!$userSpecialtyId || !$userSpecialty) {
            Toast::warning('Будь ласка, спочатку оберіть свою спеціальність');
            return;
        }

        $userId = Auth::id();

        $userSpecialtySubject = UserSpecialtySubject::where([
            'user_specialty_id' => $userSpecialtyId,
            'subject_id' => $subjectId,
        ])->first();

        if ($semester > 0) {
            $subject = Subject::find($subjectId);
            if ($subject && !$subject->active && !$userSpecialtySubject) {
                Toast::warning("Дисципліна «{$subjectName}» неактивна для нового вибору у поточному навчальному році.");
                return;
            }

            // Підраховуємо скільки предметів вже вибрано для цього семестру
            $selectedSubjectsCount = UserSpecialtySubject::where('user_specialty_id', $userSpecialtyId)
                ->where('semester', $semester)
                ->count();

            $groupLimit = $userSpecialty->group?->semesterLimits
                ?->firstWhere('semester', $semester)?->max_subjects ?? 0;

            if ($selectedSubjectsCount >= $groupLimit) {
                Toast::warning("Ви вже вибрали максимальну кількість предметів для {$semester} семестру ({$groupLimit})");
                return;
            }

            $userSpecialtySubjectData = [
                'user_id' => $userId,
                'user_specialty_id' => $userSpecialtyId,
                'subject_id' => $subjectId,
                'semester' => $semester,
                'is_student_choice' => $userSpecialty->user_id == $userId
            ];

            $userSpecialtySubject = UserSpecialtySubject::updateOrCreate(
                ['user_specialty_id' => $userSpecialtyId, 'subject_id' => $subjectId],
                $userSpecialtySubjectData
            );

            Toast::success("Дисципліна обрана «{$subjectName}» на {$semester} семестр");
        } else {
            if ($userSpecialtySubject) {
                $userSpecialtySubject->delete();
                Toast::error("Дисципліна скасована «{$subjectName}»");

                // Логування скасування дисципліни
                activity()
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'subject_name' => $subjectName,
                        'specialty_name' => $userSpecialty->specialty,
                        'is_student_choice' => $userSpecialtySubject->is_student_choice
                    ])
                    ->log(
                        $userSpecialtySubject->is_student_choice
                            ? "Студент: {$userSpecialty->full_name} скасував дисципліну: {$subjectName} ({$userSpecialty->specialty})"
                            : "Дисципліна: {$subjectName} ({$userSpecialty->specialty}) скасована адміністратором за студента: {$userSpecialty->full_name}"
                    );
            }
            return;
        }
    }

    public function chooseSpecialty($id = null, $text = null)
    {
        $id = $id ?? request('id');
        $text = $text ?? request('text');

        if ($id) {
            Cookie::queue('user_specialty_id', $id, 1440);
        }
        Toast::info("Вибрано: " . $text);

        // Логування вибору спеціальності
        activity()
            ->causedBy(Auth::user())
            ->withProperties([
                'specialty_name' => $text   // використовуємо ім'я спеціальності
            ])
            ->log("Вибір спеціальності: {$text}");
    }
}
