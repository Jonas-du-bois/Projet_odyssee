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
        // Mettre à jour les types polymorphes existants vers les noms courts
        $mappings = [
            'App\\Models\\Discovery' => 'discovery',
            'App\\Models\\Event' => 'event',
            'App\\Models\\Weekly' => 'weekly',
            'App\\Models\\Novelty' => 'novelty',
            'App\\Models\\Reminder' => 'reminder',
            'App\\Models\\Unit' => 'unit',
        ];

        foreach ($mappings as $oldType => $newType) {
            DB::table('quiz_instances')
                ->where('quizable_type', $oldType)
                ->update(['quizable_type' => $newType]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les noms de classes complets
        $mappings = [
            'discovery' => 'App\\Models\\Discovery',
            'event' => 'App\\Models\\Event',
            'weekly' => 'App\\Models\\Weekly',
            'novelty' => 'App\\Models\\Novelty',
            'reminder' => 'App\\Models\\Reminder',
            'unit' => 'App\\Models\\Unit',
        ];

        foreach ($mappings as $oldType => $newType) {
            DB::table('quiz_instances')
                ->where('quizable_type', $oldType)
                ->update(['quizable_type' => $newType]);
        }
    }
};
