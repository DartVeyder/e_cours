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
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('total_hours')->nullable()->after('credits'); // Загальний обсяг години
            $table->integer('auditory_hours')->nullable()->after('total_hours'); // Всього аудиторних години
            $table->integer('lecture_hours')->nullable()->after('auditory_hours'); // Лекції години
            $table->integer('practical_hours')->nullable()->after('lecture_hours'); // Практичні (семінарські) години
            $table->integer('laboratory_hours')->nullable()->after('practical_hours'); // Лабораторні години
            $table->integer('self_study_hours')->nullable()->after('laboratory_hours'); // Самостійна робота години
            $table->string('language')->nullable()->after('not_for_op'); // Мова викладання
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn([
                'total_hours',
                'auditory_hours',
                'lecture_hours',
                'practical_hours',
                'laboratory_hours',
                'self_study_hours',
                'language',
            ]);
        });
    }
};
