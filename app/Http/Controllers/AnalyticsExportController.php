<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsExcelExport;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportController extends Controller
{
    /**
     * Stream analytics Excel report via GET request.
     */
    public function export(Request $request, AnalyticsService $analyticsService, AnalyticsExcelExport $exporter): StreamedResponse
    {
        $user = Auth::user();
        if ($user) {
            $user->loadMissing(['department', 'roles']);
        }

        $canExport = $user && (
            $user->hasAccess('platform.systems.students') ||
            $user->roles->contains('slug', 'administrator') ||
            $user->roles->contains('slug', 'admin') ||
            $user->roles->contains('slug', 'dekanat')
        );

        if (!$canExport) {
            abort(403, 'Доступ до експорту аналітики заборонено.');
        }

        $filters = [
            'entry_year' => $request->get('entry_year', '2026'),
            'degree'     => $request->get('degree', 'Магістр'),
            'department' => $request->get('department'),
            'study_form' => $request->get('study_form'),
        ];

        $analytics = $analyticsService->getAnalyticsData($filters, $user);

        if ($user) {
            activity()
                ->causedBy($user)
                ->withProperties(['filters' => $filters])
                ->log("Експорт аналітики вибору дисциплін у Excel");
        }

        return $exporter->export($analytics);
    }
}
