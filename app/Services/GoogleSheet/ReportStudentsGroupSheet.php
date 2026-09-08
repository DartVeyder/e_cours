<?php

namespace App\Services\GoogleSheet;

class ReportStudentsGroupSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return GoogleSheetService::getSheetId('report_groups');
    }

    public function __construct()
    {
        parent::__construct('Аркуш1');
    }

}
