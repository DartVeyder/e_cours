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
        Schema::create('group_semester_limits', function (Blueprint $table) {
            $table->id();

            // Група, до якої належить обмеження
            $table->foreignId('group_id')
                ->constrained('groups')
                ->cascadeOnDelete();

            // Номер семестру (1, 2, 3...)
            $table->unsignedInteger('semester');

            // Максимальна кількість предметів, які можна вибрати
            $table->unsignedInteger('max_subjects')->default(0);

            $table->timestamps();

            // Унікальність: одна група — одне обмеження на семестр
            $table->unique(['group_id', 'semester']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_semester_limits');
    }
};
