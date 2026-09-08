<?php

namespace App\Services\GoogleSheet;

class GroupsSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return GoogleSheetService::getSheetId('groups');
    }

    public function __construct()
    {
        $tab = GoogleSheetService::getSheetTab('groups', 'Група');
        parent::__construct($tab);
    }

    protected function getHeadersMap(): array
    {
        return    [
            'Група' => 'group',
            'Кількість вибіркових' => 'electiveCount',
            'Тип' => 'study_form',
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
