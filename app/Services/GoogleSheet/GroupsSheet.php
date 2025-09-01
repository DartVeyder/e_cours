<?php

namespace App\Services\GoogleSheet;

class GroupsSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return '1mgLhc_jg_XSFbXjqx32xLzXTapHNMyR1kF9xASkHh_A';
    }

    public function __construct()
    {
        parent::__construct('Група');
    }

    protected function getHeadersMap(): array
    {
        return    [
            'Група' => 'group',
            'Кількість вибіркових' => 'electiveCount'
        ];
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
