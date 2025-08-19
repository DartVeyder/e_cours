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
        Schema::create('user_specialties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Створюємо поле для зв'язку з таблицею users
            $table->string('full_name'); // Повне ім'я
            $table->date('birth_date')->nullable(); // Дата народження
            $table->string('degree'); // Ступінь
            $table->string('specialty'); // Спеціальність
            $table->string('education_program'); // Освітня програма
            $table->string('funding'); // Фінансування
            $table->string('education_form'); // Форма здобуття освіти
            $table->integer('code_edebo'); // Код ЕДЕБО
            $table->string('group'); // Група
            $table->string('email'); // Поле для email (може бути NULL)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_specialties');
    }
};
