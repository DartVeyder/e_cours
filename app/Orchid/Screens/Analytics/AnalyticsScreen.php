<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Analytics;

use App\Services\AnalyticsExcelExport;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsScreen extends Screen
{
    /**
     * Display header name.
     */
    public function name(): ?string
    {
        return 'Аналітика вибору дисциплін';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'Моніторинг вибору вибіркових навчальних дисциплін (магістри 2026 року вступу та інші когорти)';
    }

    /**
     * The permissions required to access this screen.
     */
    public function permission(): ?iterable
    {
        return [
            'platform.systems.students',
        ];
    }

    /**
     * Disable unsaved changes warning on analytics dashboard.
     */
    public function needPreventsAbandonment(): bool
    {
        return false;
    }

    /**
     * Query data.
     */
    public function query(Request $request, AnalyticsService $analyticsService): iterable
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing(['department', 'roles']);
        }

        // Filters with defaults for Masters 2026
        $filters = [
            'entry_year' => $request->get('entry_year', '2026'),
            'degree'     => $request->get('degree', 'Магістр'),
            'department' => $request->get('department'),
            'study_form' => $request->get('study_form'),
        ];

        $analytics = $analyticsService->getAnalyticsData($filters, $user);

        return [
            'analytics' => $analytics,
        ];
    }

    /**
     * Action buttons for screen.
     */
    public function commandBar(): iterable
    {
        $params = request()->only(['entry_year', 'degree', 'department', 'study_form']);

        return [
            Link::make('Експорт в Excel (.xlsx)')
                ->icon('bs.cloud-download')
                ->href(route('export.analytics.excel', $params)),

            Link::make('Список студентів')
                ->icon('bs.people')
                ->route('platform.students'),
        ];
    }

    /**
     * Views for the screen.
     */
    public function layout(): iterable
    {
        return [
            Layout::view('analytics.index'),
        ];
    }

    /**
     * Export analytics summary to Excel.
     */
    public function exportExcel(Request $request, AnalyticsService $analyticsService, AnalyticsExcelExport $exporter): StreamedResponse
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing(['department', 'roles']);
        }

        $filters = [
            'entry_year' => $request->get('entry_year', '2026'),
            'degree'     => $request->get('degree', 'Магістр'),
            'department' => $request->get('department'),
            'study_form' => $request->get('study_form'),
        ];

        $analytics = $analyticsService->getAnalyticsData($filters, $user);

        activity()
            ->causedBy($user)
            ->withProperties(['filters' => $filters])
            ->log("Експорт аналітики вибору дисциплін у Excel");

        return $exporter->export($analytics);
    }
}
