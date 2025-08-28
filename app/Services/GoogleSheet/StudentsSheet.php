<?php

namespace App\Services\GoogleSheet;

class StudentsSheet extends GoogleSheetModel
{
    protected function getSpreadsheetId(): string
    {
        return '1mgLhc_jg_XSFbXjqx32xLzXTapHNMyR1kF9xASkHh_A';
    }

    public function __construct()
    {
        parent::__construct('Студенти');
    }

    protected function getHeadersMap(): array
    {
        return    [
            'Електронна пошта' => 'email',
            'ID картки' => 'card_id',
            'Статус з' => 'status_from',
            'Статус навчання' => 'study_status',
            'ID ФО' => 'fo_id',
            'Здобувач' => 'full_name',
            'Дата народження' => 'birth_date',
            'Тип ДПО' => 'dpo_type',
            'Серія документа' => 'document_series',
            'Номер документа' => 'document_number',
            'Дата видачі' => 'issue_date',
            'Дійсний до' => 'valid_until',
            'Стать' => 'gender',
            'Громадянство' => 'citizenship',
            'ПІБ англійською' => 'name_en',
            'РНОКПП' => 'rnokpp',
            'Валідний РНОКПП' => 'valid_rnokpp',
            'Рік ліцензійних обсягів' => 'license_year',
            'Початок навчання' => 'study_start',
            'Завершення навчання' => 'study_end',
            'Дата прийому на навчання на наступний рівень освіти або офіційного засвідчення отриманих документів про освіту' => 'next_level_admission_date',
            'Структурний підрозділ' => 'department',
            'Чи здобуває освітній ступінь за дуальною формою навчання' => 'dual_form',
            'Освітній ступінь (рівень)' => 'degree',
            'Вступ на основі' => 'admission_basis',
            'Форма навчання' => 'study_form',
            'Джерело фінансування' => 'funding_source',
            'Чи здобувався ступень за іншою спеціальністю' => 'other_specialty',
            'Чи скорочений термін навчання' => 'shortened_term',
            'Спеціальність' => 'specialty',
            'Спеціалізація' => 'specialization',
            'ID ОП' => 'op_id',
            'Освітня програма' => 'education_program',
            'Професія' => 'profession',
            'Курс' => 'course',
            'Група' => 'group',
            'Тип іноземця' => 'foreigner_type',
            'Код категорії' => 'category_code',
            'Чи є в картці документ про освіту' => 'has_education_doc',
            'Чи є в картці студенський (учнівський) квиток' => 'has_student_card',
            'Чи є в картці академічна довідка (освітня декларація)' => 'has_academic_reference',
            'Причина відрахування' => 'expulsion_reason',
            'Підстави надання академічної відпустки' => 'academic_leave_reason',
            'Статус по' => 'status_to',
            'Статус диплома в замовленні документів' => 'diploma_status',
            'Статус студенського (учнівського) квитка в замовленні документів' => 'student_card_status',
            'Статус свідоцтва про присвоєння (підвищення) робітничої кваліфікації в замовленні документів' => 'qualification_certificate_status',
            'Рік бюджету' => 'budget_year',
            'Чи регіональне замовлення' => 'regional_order',
            'Наказ про зарахування' => 'enrollment_order',
            'Попередній заклад освіти' => 'previous_institution',
            'Документ про освіту' => 'previous_education_doc',
            'Інформація про попереднє навчання' => 'previous_study_info',
            'Академічна довідка (освітня декларація)' => 'has_academic_reference_doc',
            'Академічна довідка при відрахуванні' => 'has_expulsion_reference',
            'Студентський (учнівський) квиток' => 'has_student_ticket',
            'Виданий диплом' => 'has_diploma',
            'Інформація про зарахування' => 'enrollment_info',
            'КБ при вступі' => 'kb_entry',
            'КР без отримання ПЗСО' => 'kr_without_pzso',
            'Час останньої зміни' => 'last_update',
            'Код категорії переведення на бюджет' => 'budget_transfer_category_code',
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
