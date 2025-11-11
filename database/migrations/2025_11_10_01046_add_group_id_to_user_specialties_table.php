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
            $table->foreignId('group_id')
                ->nullable()
                ->after('department_id')
                ->constrained('groups')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_specialties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
    }
};
