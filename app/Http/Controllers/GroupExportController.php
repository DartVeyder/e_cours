<?php

namespace App\Http\Controllers;

use App\Services\GroupExcelExport;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GroupExportController extends Controller
{
    public function exportExcel(string $group): StreamedResponse
    {
        activity()
            ->causedBy(Auth::user())
            ->withProperties(['group' => $group])
            ->log("Вивантаження Excel-звіту по групі «{$group}»");

        return (new GroupExcelExport())->export($group);
    }
}
