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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Дисципліна
            $table->string('department')->nullable(); // Кафедра
            $table->text('annotation')->nullable(); // Анотація
            $table->string('control_type')->nullable(); // Вид контролю
            $table->integer('credits')->nullable(); // Кількість кредитів
            $table->string('status')->nullable(); // Статус дисципліни
            $table->string('semester')->nullable(); // Вивчення у семестрі
            $table->string('max_min_students')->nullable(); // Макс. кількість
            $table->text('not_for_op')->nullable(); // Для яких ОП не може читатися
            $table->string('code')->nullable();
            $table->boolean('active')->default(1);
            $table->string('education_level')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
