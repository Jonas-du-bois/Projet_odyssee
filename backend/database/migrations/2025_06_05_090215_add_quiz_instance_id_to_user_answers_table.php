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
        Schema::table('user_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('quiz_instance_id')->nullable()->after('user_id');
            $table->foreign('quiz_instance_id')->references('id')->on('quiz_instances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_answers', function (Blueprint $table) {
            $table->dropForeign(['quiz_instance_id']);
            $table->dropColumn('quiz_instance_id');
        });
    }
};
