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
            $table->string('code')->nullable()->after('not_for_op');
            $table->boolean('active')->default(1)->after('not_for_op');
            $table->string('education_level')->nullable()->after('not_for_op');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['code', 'active', 'education_level']);
        });
    }
};
