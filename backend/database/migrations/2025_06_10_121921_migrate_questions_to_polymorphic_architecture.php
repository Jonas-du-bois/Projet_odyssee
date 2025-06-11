<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ajouter les nouvelles colonnes polymorphiques
        Schema::table('questions', function (Blueprint $table) {
            $table->string('quizable_type')->nullable()->after('id');
            $table->unsignedBigInteger('quizable_id')->nullable()->after('quizable_type');
            $table->text('question_text')->nullable()->after('quizable_id');
            $table->json('options')->nullable()->after('question_text');
            $table->string('correct_answer')->nullable()->after('options');
            
            // Index pour la relation polymorphique
            $table->index(['quizable_type', 'quizable_id']);
        });

        // 2. Migrer les données existantes de unit_id vers le système polymorphique
        DB::statement("
            UPDATE questions 
            SET quizable_type = 'App\\\\Models\\\\Unit',
                quizable_id = unit_id,
                question_text = statement
            WHERE unit_id IS NOT NULL
        ");

        // 3. Supprimer l'ancienne colonne après migration des données
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'statement']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer l'ancienne structure
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('cascade');
            $table->text('statement')->nullable();
        });

        // Migrer les données en arrière
        DB::statement("
            UPDATE questions 
            SET unit_id = quizable_id,
                statement = question_text
            WHERE quizable_type = 'App\\\\Models\\\\Unit'
        ");

        // Supprimer les nouvelles colonnes
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['quizable_type', 'quizable_id']);
            $table->dropColumn(['quizable_type', 'quizable_id', 'question_text', 'options', 'correct_answer']);
        });
    }
};
