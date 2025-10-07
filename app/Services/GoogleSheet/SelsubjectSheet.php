<?php

namespace App\Services\GoogleSheet;

class SelsubjectSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return '1DeCO1hKHqcYPcriPcaIz3LAZVCFKpmfjdkspNu1Is2w';
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
            "Загальний обсяг години" => "total_hours",
            "Всього аудоторних години" => "auditory_hours",
            "Лекції години3" => "lecture_hours",
            "Практичні (семінарські) години " => "practical_hours",
            "Лабораторні години" => "laboratory_hours",
            "Самостійна робота години" => "self_study_hours",
            "Вивчення у семестрі" => "semester",
            "Макс/мін. кількість здобувачів" => "max_min_students",
            "Для яких ОП не може читатися" => "not_for_op",
            "Мова викладання українська/англійська" => "language",
            "Шифр" => "code",
            "Активна" => "active",
            "Рівень освіти" => "education_level",
        ];
    }


    public function __construct()
    {
        parent::__construct('Всі!B1:T');
    }


}
