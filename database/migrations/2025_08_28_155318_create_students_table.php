<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Створюємо поле для зв'язку з таблицею users
            $table->string('email'); // Email
            $table->string('card_id')->unique(); // ID картки ЕДЕБО
            $table->string('status_from')->nullable(); // Статус з
            $table->string('study_status')->nullable(); // Статус навчання
            $table->unsignedBigInteger('fo_id')->nullable(); // ID ФО
            $table->string('full_name')->nullable(); // Здобувач
            $table->date('birth_date')->nullable(); // Дата народження
            $table->string('dpo_type')->nullable(); // Тип ДПО
            $table->string('document_series')->nullable(); // Серія документа
            $table->string('document_number')->nullable(); // Номер документа
            $table->date('issue_date')->nullable(); // Дата видачі
            $table->date('valid_until')->nullable(); // Дійсний до
            $table->string('gender')->nullable(); // Стать
            $table->string('citizenship')->nullable(); // Громадянство
            $table->string('name_en')->nullable(); // ПІБ англійською
            $table->string('rnokpp')->nullable(); // РНОКПП
            $table->string('valid_rnokpp')->nullable(); // Валідний РНОКПП
            $table->string('license_year')->nullable(); // Рік ліцензійних обсягів
            $table->date('study_start')->nullable(); // Початок навчання
            $table->date('study_end')->nullable(); // Завершення навчання
            $table->date('next_level_admission_date')->nullable(); // Дата прийому на навчання на наступний рівень
            $table->string('department')->nullable(); // Структурний підрозділ
            $table->string('dual_form')->nullable(); // Чи здобуває за дуальною формою
            $table->string('degree')->nullable(); // Освітній ступінь
            $table->string('admission_basis')->nullable(); // Вступ на основі
            $table->string('study_form')->nullable(); // Форма навчання
            $table->string('funding_source')->nullable(); // Джерело фінансування
            $table->string('other_specialty')->nullable(); // Чи здобувався ступень за іншою спец.
            $table->string('shortened_term')->nullable(); // Чи скорочений термін
            $table->string('specialty')->nullable(); // Спеціальність
            $table->string('specialization')->nullable(); // Спеціалізація
            $table->unsignedBigInteger('op_id')->nullable(); // ID ОП
            $table->string('education_program')->nullable(); // Освітня програма
            $table->string('profession')->nullable(); // Професія
            $table->string('course')->nullable(); // Курс
            $table->string('group')->nullable(); // Група
            $table->string('foreigner_type')->nullable(); // Тип іноземця
            $table->string('category_code')->nullable(); // Код категорії
            $table->string('has_education_doc')->nullable(); // Чи є документ про освіту
            $table->string('has_student_card')->nullable(); // Чи є студентський квиток
            $table->string('has_academic_reference')->nullable(); // Чи є академічна довідка
            $table->string('expulsion_reason')->nullable(); // Причина відрахування
            $table->string('academic_leave_reason')->nullable(); // Підстави надання акад. відпустки
            $table->string('status_to')->nullable(); // Статус по
            $table->string('diploma_status')->nullable(); // Статус диплома
            $table->string('student_card_status')->nullable(); // Статус студентського квитка
            $table->string('qualification_certificate_status')->nullable(); // Статус свідоцтва про кваліфікацію
            $table->string('budget_year')->nullable(); // Рік бюджету
            $table->string('regional_order')->nullable(); // Чи регіональне замовлення
            $table->text('enrollment_order')->nullable(); // Наказ про зарахування
            $table->string('previous_institution')->nullable(); // Попередній заклад
            $table->text('previous_education_doc')->nullable(); // Документ про освіту
            $table->text('previous_study_info')->nullable(); // Інформація про попереднє навчання
            $table->string('has_academic_reference_doc')->nullable(); // Академічна довідка
            $table->string('has_expulsion_reference')->nullable(); // Академічна довідка при відрахуванні
            $table->string('has_student_ticket')->nullable(); // Студентський квиток
            $table->string('has_diploma')->nullable(); // Виданий диплом
            $table->text('enrollment_info')->nullable(); // Інформація про зарахування
            $table->string('kb_entry')->nullable(); // КБ при вступі
            $table->string('kr_without_pzso')->nullable(); // КР без отримання ПЗСО
            $table->timestamp('last_update')->nullable(); // Час останньої зміни
            $table->string('budget_transfer_category_code')->nullable(); // Код категорії переведення на бюджет

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
