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
        Schema::table('user_specialty_subjects', function (Blueprint $table) {
            $table->boolean('is_student_choice')
                ->default(true)
                ->after('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_specialty_subjects', function (Blueprint $table) {
            $table->dropColumn('is_student_choice');
        });
    }
};
