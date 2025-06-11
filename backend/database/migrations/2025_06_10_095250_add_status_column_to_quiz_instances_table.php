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
        Schema::table('quiz_instances', function (Blueprint $table) {
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active')->after('speed_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_instances', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
