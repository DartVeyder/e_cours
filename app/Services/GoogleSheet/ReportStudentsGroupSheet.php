<?php

namespace App\Services\GoogleSheet;

class ReportStudentsGroupSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return '1HRqXSekhpmGR-r41L04ePvTGZWaFFIeWpGzq1IwH9EI';
    }

    public function __construct()
    {
        parent::__construct('Аркуш1');
    }

}
