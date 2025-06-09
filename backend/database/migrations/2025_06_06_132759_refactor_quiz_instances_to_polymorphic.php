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
        Schema::table('quiz_instances', function (Blueprint $table) {
            // Ajouter les colonnes polymorphes
            $table->string('quizable_type')->nullable()->after('quiz_type_id');
            $table->unsignedBigInteger('quizable_id')->nullable()->after('quizable_type');
            
            // Ajouter le mode de quiz pour différencier les types d'interactions
            $table->string('quiz_mode')->default('quiz')->after('quizable_id');
        });

        // Migrer les données existantes
        DB::statement("
            UPDATE quiz_instances 
            SET quizable_type = CASE 
                WHEN module_type = 'Discovery' THEN 'App\\\\Models\\\\Discovery'
                WHEN module_type = 'Event' THEN 'App\\\\Models\\\\Event'
                WHEN module_type = 'Weekly' THEN 'App\\\\Models\\\\Weekly'
                WHEN module_type = 'Novelty' THEN 'App\\\\Models\\\\Novelty'
                WHEN module_type = 'Reminder' THEN 'App\\\\Models\\\\Reminder'
                WHEN module_type = 'Unit' THEN 'App\\\\Models\\\\Unit'
                ELSE 'App\\\\Models\\\\Discovery'
            END,
            quizable_id = module_id
            WHERE module_type IS NOT NULL AND module_id IS NOT NULL
        ");

        // Supprimer les anciennes colonnes après migration des données
        Schema::table('quiz_instances', function (Blueprint $table) {
            $table->dropColumn(['module_type', 'module_id']);
        });

        // Ajouter un index composite pour les performances
        Schema::table('quiz_instances', function (Blueprint $table) {
            $table->index(['quizable_type', 'quizable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_instances', function (Blueprint $table) {
            // Remettre les anciennes colonnes
            $table->string('module_type')->nullable();
            $table->integer('module_id')->nullable();
            
            // Supprimer les nouvelles colonnes
            $table->dropIndex(['quizable_type', 'quizable_id']);
            $table->dropColumn(['quizable_type', 'quizable_id', 'quiz_mode']);
        });
    }
};
