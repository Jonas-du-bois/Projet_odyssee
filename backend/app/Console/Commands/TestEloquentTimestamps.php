<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TestEloquentTimestamps extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'breitling:test-eloquent-timestamps {--model= : Test specific model}';

    /**
     * The console command description.
     */
    protected $description = 'Test Eloquent models timestamps functionality with database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Test des timestamps Eloquent avec la base de données...');

        $modelsToTest = $this->option('model') 
            ? ['App\\Models\\' . $this->option('model')]
            : $this->getTestableModels();

        $results = [];
        
        foreach ($modelsToTest as $modelClass) {
            $result = $this->testModelTimestamps($modelClass);
            $results[] = $result;
        }

        $this->displayResults($results);

        $passed = collect($results)->where('passed', true)->count();
        $total = count($results);

        $this->newLine();
        $this->info("📊 Résultats: {$passed}/{$total} modèles passent les tests timestamps");

        return $passed === $total ? Command::SUCCESS : Command::FAILURE;
    }

    private function getTestableModels(): array
    {
        // Modèles avec des fillable et moins de risques pour les tests
        return [
            'App\\Models\\Rank',
            'App\\Models\\Chapter', 
            'App\\Models\\QuizType',
            'App\\Models\\Unit',
            'App\\Models\\Discovery',
            'App\\Models\\Novelty',
            'App\\Models\\Event',
            'App\\Models\\Reminder',
            'App\\Models\\Weekly',
            'App\\Models\\LastChance'
        ];
    }

    private function testModelTimestamps(string $modelClass): array
    {
        $modelName = class_basename($modelClass);
        
        try {
            if (!class_exists($modelClass)) {
                return [
                    'model' => $modelName,
                    'passed' => false,
                    'error' => 'Class does not exist'
                ];
            }

            $model = new $modelClass;
            $tableName = $model->getTable();

            // Vérifier si la table existe
            if (!Schema::hasTable($tableName)) {
                return [
                    'model' => $modelName,
                    'passed' => false,
                    'error' => 'Table does not exist'
                ];
            }

            // Test si le modèle utilise les timestamps
            if (!$model->usesTimestamps()) {
                return [
                    'model' => $modelName,
                    'passed' => true,
                    'note' => 'Timestamps disabled (as expected)'
                ];
            }

            // Test création avec timestamps automatiques
            return $this->testCreateWithTimestamps($model, $modelName);

        } catch (\Exception $e) {
            return [
                'model' => $modelName,
                'passed' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function testCreateWithTimestamps($model, string $modelName): array
    {
        try {
            // Préparer les données de test selon le modèle
            $testData = $this->getTestDataForModel($modelName);
            
            if (empty($testData)) {
                return [
                    'model' => $modelName,
                    'passed' => true,
                    'note' => 'No test data defined - skipped'
                ];
            }

            DB::beginTransaction();

            // Créer un enregistrement
            $before = Carbon::now();
            $created = $model->create($testData);
            $after = Carbon::now();

            // Vérifier que created_at et updated_at sont automatiquement définis
            $hasValidCreatedAt = $created->created_at && 
                                $created->created_at->between($before, $after);
            
            $hasValidUpdatedAt = $created->updated_at && 
                                $created->updated_at->between($before, $after);

            // Test de mise à jour
            sleep(1); // S'assurer qu'updated_at change
            $updateBefore = Carbon::now();
            $created->touch(); // Déclencher une mise à jour
            $updateAfter = Carbon::now();

            $created->refresh();
            $hasValidUpdatedAtAfterUpdate = $created->updated_at && 
                                           $created->updated_at->between($updateBefore, $updateAfter);

            DB::rollback(); // Annuler les changements

            $passed = $hasValidCreatedAt && $hasValidUpdatedAt && $hasValidUpdatedAtAfterUpdate;

            return [
                'model' => $modelName,
                'passed' => $passed,
                'details' => [
                    'created_at_valid' => $hasValidCreatedAt,
                    'updated_at_valid' => $hasValidUpdatedAt,
                    'updated_at_after_update' => $hasValidUpdatedAtAfterUpdate
                ]
            ];

        } catch (\Exception $e) {
            DB::rollback();
            
            return [
                'model' => $modelName,
                'passed' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function getTestDataForModel(string $modelName): array
    {
        $testData = [
            'Rank' => [
                'name' => 'Test Rank',
                'level' => 99,
                'minimum_points' => 9999
            ],
            'Chapter' => [
                'title' => 'Test Chapter',
                'description' => 'Test description',
                'is_active' => true
            ],
            'QuizType' => [
                'name' => 'Test Quiz Type',
                'base_points' => 10,
                'speed_bonus' => true,
                'gives_ticket' => false,
                'bonus_multiplier' => 1
            ],
            'Discovery' => [
                'chapter_id' => 1, // Assume chapter 1 exists
                'available_date' => Carbon::today()
            ],
            'Novelty' => [
                'chapter_id' => 1,
                'publication_date' => Carbon::today(),
                'initial_bonus' => true
            ],
            'Event' => [
                'theme' => 'Test Event',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::tomorrow()
            ],
            'Reminder' => [
                'chapter_id' => 1,
                'number_questions' => 5,
                'deadline_date' => Carbon::tomorrow()
            ],
            'Weekly' => [
                'chapter_id' => 1,
                'week_start' => Carbon::today(),
                'number_questions' => 10
            ],
            'LastChance' => [
                'name' => 'Test Last Chance',
                'start_date' => Carbon::today(),
                'end_date' => Carbon::tomorrow()
            ]
        ];

        return $testData[$modelName] ?? [];
    }

    private function displayResults(array $results): void
    {
        $this->newLine();
        
        $tableData = [];
        foreach ($results as $result) {
            $status = $result['passed'] ? '✅ PASS' : '❌ FAIL';
            $details = '';
            
            if (isset($result['error'])) {
                $details = '❌ ' . $result['error'];
            } elseif (isset($result['note'])) {
                $details = '📝 ' . $result['note'];
            } elseif (isset($result['details'])) {
                $details = sprintf(
                    'C:%s U:%s UU:%s',
                    $result['details']['created_at_valid'] ? '✅' : '❌',
                    $result['details']['updated_at_valid'] ? '✅' : '❌',
                    $result['details']['updated_at_after_update'] ? '✅' : '❌'
                );
            }

            $tableData[] = [
                $result['model'],
                $status,
                $details
            ];
        }

        $this->table(['Modèle', 'Statut', 'Détails'], $tableData);
    }
}
