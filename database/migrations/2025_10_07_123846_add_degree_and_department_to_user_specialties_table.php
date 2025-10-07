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
        Schema::table('user_specialties', function (Blueprint $table) {
            // Додаємо degree_id після user_id
            $table->foreignId('degree_id')
                ->nullable()
                ->after('user_id')
                ->constrained('degrees')
                ->nullOnDelete();

            // Додаємо department_id після degree_id
            $table->foreignId('department_id')
                ->nullable()
                ->after('degree_id')
                ->constrained('departments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_specialties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('degree_id');
            $table->dropConstrainedForeignId('department_id');
        });
    }
};
