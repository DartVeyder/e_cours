<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSpecialty;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Resolve the educational program name for a student record.
     */
    public static function resolveProgramName(object $student): string
    {
        $ep = trim((string)($student->education_program ?? ''));
        if (!empty($ep) && $ep !== '—') {
            return $ep;
        }

        $sp = trim((string)($student->specialization ?? ''));
        if (!empty($sp) && $sp !== '—') {
            return $sp;
        }

        $spec = trim((string)($student->specialty ?? ''));
        return !empty($spec) ? $spec : 'Не вказано';
    }

    /**
     * Get aggregated analytics data for subject selection.
     *
     * @param array $filters
     * @param User|null $user
     * @return array
     */
    public function getAnalyticsData(array $filters = [], ?User $user = null): array
    {
        // Resolve default filter values
        $entryYear = $filters['entry_year'] ?? '2026';
        $degree = $filters['degree'] ?? 'Магістр';
        $department = $filters['department'] ?? null;
        $studyForm = $filters['study_form'] ?? null;

        // Auto-scope for Dekanat role
        if ($user && $user->department && $user->roles->contains('slug', 'dekanat')) {
            $department = $user->department->name;
        }

        $activeFilters = [
            'entry_year' => $entryYear,
            'degree' => $degree,
            'department' => $department,
            'study_form' => $studyForm,
        ];

        // Fetch student records with selection statuses
        $students = $this->queryStudentsWithStatus($activeFilters);

        // Aggregate summary metrics
        $summary = $this->aggregateSummary($students);

        // Aggregate by Faculty (Department)
        $faculties = $this->aggregateByFaculty($students);

        // Aggregate by Specialty (with programs breakdown)
        $specialties = $this->aggregateBySpecialty($students);

        // Flat list of individual educational programs
        $educationPrograms = $specialties->flatMap(function ($s) {
            return $s['programs'];
        })->sortByDesc('total')->values();

        // Filter options for dropdowns
        $filterOptions = $this->getFilterOptions();

        return [
            'filters' => $activeFilters,
            'summary' => $summary,
            'faculties' => $faculties,
            'specialties' => $specialties,
            'educationPrograms' => $educationPrograms,
            'filterOptions' => $filterOptions,
        ];
    }

    /**
     * Fetch student records with choice status using optimized SQL subqueries.
     *
     * @param array $filters
     * @return Collection
     */
    public function queryStudentsWithStatus(array $filters): Collection
    {
        $query = DB::table('user_specialties as us')
            ->leftJoin(DB::raw('(SELECT user_specialty_id, count(*) as cnt FROM user_specialty_subjects GROUP BY user_specialty_id) as sub'), 'sub.user_specialty_id', '=', 'us.id')
            ->leftJoin(DB::raw('(SELECT group_id, SUM(max_subjects) as total_max FROM group_semester_limits GROUP BY group_id) as lim'), 'lim.group_id', '=', 'us.group_id')
            ->select([
                'us.id',
                'us.full_name',
                'us.card_id',
                'us.email',
                'us.group_name',
                'us.study_start',
                'us.study_form',
                'us.degree',
                DB::raw("COALESCE(NULLIF(TRIM(us.department), ''), 'Не вказано') as department"),
                DB::raw("COALESCE(NULLIF(TRIM(us.specialty), ''), 'Не вказано') as specialty"),
                DB::raw("COALESCE(NULLIF(TRIM(us.specialization), ''), '') as specialization"),
                DB::raw("COALESCE(NULLIF(TRIM(us.education_program), ''), '—') as education_program"),
                DB::raw('COALESCE(sub.cnt, 0) as subjects_count'),
                DB::raw('COALESCE(lim.total_max, 0) as max_subjects'),
                DB::raw("
                    CASE 
                        WHEN COALESCE(sub.cnt, 0) = 0 THEN 'none'
                        WHEN COALESCE(lim.total_max, 0) > 0 AND COALESCE(sub.cnt, 0) < COALESCE(lim.total_max, 0) THEN 'partial'
                        ELSE 'all'
                    END as choice_status
                ")
            ])
            ->whereNull('us.deleted_at');

        // Apply Year filter
        if (!empty($filters['entry_year']) && $filters['entry_year'] !== 'all') {
            $year = trim((string)$filters['entry_year']);
            $query->where('us.study_start', 'like', $year . '%');
        }

        // Apply Degree filter
        if (!empty($filters['degree']) && $filters['degree'] !== 'all') {
            $deg = trim((string)$filters['degree']);
            if (mb_stripos($deg, 'магістр') !== false) {
                $query->where(function ($q) {
                    $q->where('us.degree', 'like', '%Магістр%')
                      ->orWhere('us.degree', 'like', '%магістр%')
                      ->orWhere('us.degree', 'like', '%Магистр%')
                      ->orWhere('us.degree', 'like', '%магистр%')
                      ->orWhere('us.degree', 'like', '%Master%')
                      ->orWhere('us.degree', 'like', '%master%');
                });
            } elseif (mb_stripos($deg, 'бакалавр') !== false) {
                $query->where(function ($q) {
                    $q->where('us.degree', 'like', '%Бакалавр%')
                      ->orWhere('us.degree', 'like', '%бакалавр%')
                      ->orWhere('us.degree', 'like', '%Bachelor%')
                      ->orWhere('us.degree', 'like', '%bachelor%');
                });
            } else {
                $query->where(function ($q) use ($deg) {
                    $q->where('us.degree', $deg)
                      ->orWhere('us.degree', 'like', '%' . $deg . '%');
                });
            }
        }

        // Apply Department filter
        if (!empty($filters['department']) && $filters['department'] !== 'all') {
            $query->where('us.department', $filters['department']);
        }

        // Apply Study Form filter
        if (!empty($filters['study_form']) && $filters['study_form'] !== 'all') {
            $query->where('us.study_form', $filters['study_form']);
        }

        return $query->get();
    }

    /**
     * Compute total summary stats and percentages.
     */
    protected function aggregateSummary(Collection $students): array
    {
        $total = $students->count();
        $all = $students->where('choice_status', 'all')->count();
        $partial = $students->where('choice_status', 'partial')->count();
        $none = $students->where('choice_status', 'none')->count();

        $allPercent = $total > 0 ? round(($all / $total) * 100, 1) : 0;
        $partialPercent = $total > 0 ? round(($partial / $total) * 100, 1) : 0;
        $nonePercent = $total > 0 ? round(($none / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'all' => $all,
            'all_percent' => $allPercent,
            'partial' => $partial,
            'partial_percent' => $partialPercent,
            'none' => $none,
            'none_percent' => $nonePercent,
            'completion_rate' => $allPercent,
        ];
    }

    /**
     * Compute statistics broken down by faculty.
     */
    protected function aggregateByFaculty(Collection $students): Collection
    {
        return $students->groupBy('department')->map(function (Collection $deptStudents, string $deptName) {
            $total = $deptStudents->count();
            $all = $deptStudents->where('choice_status', 'all')->count();
            $partial = $deptStudents->where('choice_status', 'partial')->count();
            $none = $deptStudents->where('choice_status', 'none')->count();

            $allPercent = $total > 0 ? round(($all / $total) * 100, 1) : 0;
            $partialPercent = $total > 0 ? round(($partial / $total) * 100, 1) : 0;
            $nonePercent = $total > 0 ? round(($none / $total) * 100, 1) : 0;

            // Specialties inside this faculty
            $specialties = $deptStudents->groupBy('specialty')->map(function (Collection $specStudents, string $specName) use ($deptName) {
                $specTotal = $specStudents->count();
                $specAll = $specStudents->where('choice_status', 'all')->count();
                $specPartial = $specStudents->where('choice_status', 'partial')->count();
                $specNone = $specStudents->where('choice_status', 'none')->count();

                // Programs inside this specialty
                $programs = $specStudents->groupBy(function ($s) {
                    return self::resolveProgramName($s);
                })->map(function (Collection $progStudents, string $progName) use ($specName, $deptName) {
                    $pTotal = $progStudents->count();
                    $pAll = $progStudents->where('choice_status', 'all')->count();
                    $pPartial = $progStudents->where('choice_status', 'partial')->count();
                    $pNone = $progStudents->where('choice_status', 'none')->count();

                    return [
                        'name' => $progName,
                        'specialty' => $specName,
                        'department' => $deptName,
                        'total' => $pTotal,
                        'all' => $pAll,
                        'all_percent' => $pTotal > 0 ? round(($pAll / $pTotal) * 100, 1) : 0,
                        'partial' => $pPartial,
                        'partial_percent' => $pTotal > 0 ? round(($pPartial / $pTotal) * 100, 1) : 0,
                        'none' => $pNone,
                        'none_percent' => $pTotal > 0 ? round(($pNone / $pTotal) * 100, 1) : 0,
                        'completion_rate' => $pTotal > 0 ? round(($pAll / $pTotal) * 100, 1) : 0,
                    ];
                })->sortByDesc('total')->values();

                return [
                    'name' => $specName,
                    'total' => $specTotal,
                    'all' => $specAll,
                    'all_percent' => $specTotal > 0 ? round(($specAll / $specTotal) * 100, 1) : 0,
                    'partial' => $specPartial,
                    'partial_percent' => $specTotal > 0 ? round(($specPartial / $specTotal) * 100, 1) : 0,
                    'none' => $specNone,
                    'none_percent' => $specTotal > 0 ? round(($specNone / $specTotal) * 100, 1) : 0,
                    'completion_rate' => $specTotal > 0 ? round(($specAll / $specTotal) * 100, 1) : 0,
                    'is_broad' => $programs->count() > 1 || ($programs->count() === 1 && $programs->first()['name'] !== $specName),
                    'programs_count' => $programs->count(),
                    'programs' => $programs,
                ];
            })->sortByDesc('total')->values();

            return [
                'name' => $deptName,
                'total' => $total,
                'all' => $all,
                'all_percent' => $allPercent,
                'partial' => $partial,
                'partial_percent' => $partialPercent,
                'none' => $none,
                'none_percent' => $nonePercent,
                'completion_rate' => $allPercent,
                'specialties_count' => $specialties->count(),
                'specialties' => $specialties,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * Compute statistics broken down by specialty across all faculties.
     */
    protected function aggregateBySpecialty(Collection $students): Collection
    {
        return $students->groupBy(function ($student) {
            return $student->department . '||' . $student->specialty;
        })->map(function (Collection $specStudents) {
            $first = $specStudents->first();
            $total = $specStudents->count();
            $all = $specStudents->where('choice_status', 'all')->count();
            $partial = $specStudents->where('choice_status', 'partial')->count();
            $none = $specStudents->where('choice_status', 'none')->count();

            $allPercent = $total > 0 ? round(($all / $total) * 100, 1) : 0;
            $partialPercent = $total > 0 ? round(($partial / $total) * 100, 1) : 0;
            $nonePercent = $total > 0 ? round(($none / $total) * 100, 1) : 0;

            // Educational programs inside this specialty
            $programs = $specStudents->groupBy(function ($s) {
                return self::resolveProgramName($s);
            })->map(function (Collection $progStudents, string $progName) use ($first) {
                $pTotal = $progStudents->count();
                $pAll = $progStudents->where('choice_status', 'all')->count();
                $pPartial = $progStudents->where('choice_status', 'partial')->count();
                $pNone = $progStudents->where('choice_status', 'none')->count();

                return [
                    'name' => $progName,
                    'specialty' => $first->specialty,
                    'department' => $first->department,
                    'total' => $pTotal,
                    'all' => $pAll,
                    'all_percent' => $pTotal > 0 ? round(($pAll / $pTotal) * 100, 1) : 0,
                    'partial' => $pPartial,
                    'partial_percent' => $pTotal > 0 ? round(($pPartial / $pTotal) * 100, 1) : 0,
                    'none' => $pNone,
                    'none_percent' => $pTotal > 0 ? round(($pNone / $pTotal) * 100, 1) : 0,
                    'completion_rate' => $pTotal > 0 ? round(($pAll / $pTotal) * 100, 1) : 0,
                ];
            })->sortByDesc('total')->values();

            $isBroad = $programs->count() > 1 || ($programs->count() === 1 && $programs->first()['name'] !== $first->specialty);

            return [
                'name' => $first->specialty,
                'department' => $first->department,
                'is_broad' => $isBroad,
                'programs_count' => $programs->count(),
                'programs' => $programs,
                'education_programs' => $programs->pluck('name')->all(),
                'total' => $total,
                'all' => $all,
                'all_percent' => $allPercent,
                'partial' => $partial,
                'partial_percent' => $partialPercent,
                'none' => $none,
                'none_percent' => $nonePercent,
                'completion_rate' => $allPercent,
            ];
        })->sortByDesc('total')->values();
    }

    /**
     * Collect available filter options from the database.
     */
    public function getFilterOptions(): array
    {
        $years = UserSpecialty::getEntryYearsOptions();
        if (!isset($years['2026'])) {
            $years = ['2026' => '2026'] + $years;
        }

        $degrees = UserSpecialty::whereNotNull('degree')
            ->distinct()
            ->pluck('degree')
            ->map(fn($d) => trim((string)$d))
            ->filter(fn($d) => !empty($d))
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (empty($degrees)) {
            $degrees = ['Магістр', 'Бакалавр'];
        } elseif (!in_array('Магістр', $degrees)) {
            array_unshift($degrees, 'Магістр');
        }

        $departments = UserSpecialty::whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->map(fn($d) => trim((string)$d))
            ->filter(fn($d) => !empty($d))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $studyForms = UserSpecialty::whereNotNull('study_form')
            ->distinct()
            ->pluck('study_form')
            ->map(fn($f) => trim((string)$f))
            ->filter(fn($f) => !empty($f))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return [
            'entry_years' => $years,
            'degrees' => $degrees,
            'departments' => $departments,
            'study_forms' => $studyForms,
        ];
    }
}
