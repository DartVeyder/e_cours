<?php

namespace App\Services\GoogleSheet;

class StudentsSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return '1Ky8MYRy03vCAkl-h0jjhh8SYJ_0nHqMdFOk_PfX7Kd4';
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
