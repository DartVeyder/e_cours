<?php

namespace App\Services\GoogleSheet;

class SelsubjectSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return '18AERbOkFBsTW-sS7SNEWaxebsEtSnjsn_2QoPAQwGds';
    }

    protected function getHeadersMap(): array
    {
        return [
            "Дисципліна" => "name",
            "Кафедра" => "department",
            "Анотація" => "annotation",
            "Вид контролю" => "control_type",
            "Кількість кредитів" => "credits",
            "Статус дисц. (загальної підготовки (ЗП) чи професійно-орієнтована (ПО))" => "status",
            "Вивчення у семестрі" => "semester",
            "Макс/мін. кількість здобувачів" => "max_min_students",
            "Для яких ОП не може читатися" => "not_for_op",
            "Рівень освіти" => "education_level",
            "Активна" => "active",
            "Шифр" => "code",
        ];
    }


    public function __construct()
    {
        parent::__construct('Всі!B1:M');
    }


}
