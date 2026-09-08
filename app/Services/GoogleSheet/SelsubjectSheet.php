<?php

namespace App\Services\GoogleSheet;

class SelsubjectSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return GoogleSheetService::getSheetId('subjects');
    }

    protected function getHeadersMap(): array
    {
        return [
            "Дисципліна" => "name",
            "Кафедра" => "chair",
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
            "Вивчення у семестрі" => "study_semester",
            "Макс/мін. кількість здобувачів" => "max_min_students",
            "Для яких ОП не може читатися" => "not_for_op",
            "Мова викладання українська/англійська" => "language",
            "Шифр" => "code",
            "Активна" => "active",
            "Рівень освіти" => "education_level",
            "Робоча програма" => "work_program",
        ];
    }


    public function __construct()
    {
        $tab = GoogleSheetService::getSheetTab('subjects', 'Всі!B1:U');
        if (!str_contains($tab, '!')) {
            $tab = "{$tab}!B1:U";
        }
        parent::__construct($tab);
    }


}
