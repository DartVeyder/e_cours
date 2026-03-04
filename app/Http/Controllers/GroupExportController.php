<?php

namespace App\Http\Controllers;

use App\Services\GroupExcelExport;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GroupExportController extends Controller
{
    public function exportExcel(string $group): StreamedResponse
    {
        return (new GroupExcelExport())->export($group);
    }
}
