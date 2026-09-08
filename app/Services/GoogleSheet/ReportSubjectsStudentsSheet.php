<?php

namespace App\Services\GoogleSheet;

class ReportSubjectsStudentsSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return GoogleSheetService::getSheetId('report_subjects');
    }

    public function __construct()
    {
        parent::__construct('Аркуш1');
    }

    public function getStudentByEmail(string $email): array
    {
        $students = $this->readAssoc();
        $matched = [];

        foreach ($students as $student) {
            if ($student['email'] === $email) {
                $matched[] = $student;
            }
        }

        return $matched; // Повертаємо всі знайдені збіги
    }

}
