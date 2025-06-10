<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidateTimestampsConfiguration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'breitling:validate-timestamps {--fix : Fix timestamp inconsistencies automatically}';

    /**
     * The console command description.
     */
    protected $description = 'Validate that Eloquent models timestamps configuration matches database schema';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🕐 Validation de la configuration des timestamps...');

        $models = $this->getModels();
        $issues = [];
        $fixed = 0;

        foreach ($models as $modelClass) {
            $result = $this->validateModel($modelClass);
            if ($result['hasIssue']) {
                $issues[] = $result;
                
                if ($this->option('fix')) {
                    if ($this->fixModel($result)) {
                        $fixed++;
                        $this->info("✅ Corrigé: {$result['modelName']}");
                    }
                }
            }
        }

        $this->displayResults($issues, $fixed);

        return empty($issues) ? Command::SUCCESS : Command::FAILURE;
    }

    private function getModels(): array
    {
        $modelFiles = File::files(app_path('Models'));
        $models = [];

        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\' . $file->getFilenameWithoutExtension();
            if (class_exists($className)) {
                $models[] = $className;
            }
        }

        return $models;
    }

    private function validateModel(string $modelClass): array
    {
        try {
            $model = new $modelClass;
            $tableName = $model->getTable();
            $modelName = class_basename($modelClass);
            
            // Vérifier si la table existe
            if (!Schema::hasTable($tableName)) {
                return [
                    'modelName' => $modelName,
                    'tableName' => $tableName,
                    'hasIssue' => true,
                    'issue' => 'Table does not exist',
                    'modelUsesTimestamps' => null,
                    'tableHasTimestamps' => false
                ];
            }

            // Vérifier si le modèle utilise les timestamps
            $modelUsesTimestamps = $model->usesTimestamps();
            
            // Vérifier si la table a les colonnes timestamps
            $tableHasCreatedAt = Schema::hasColumn($tableName, 'created_at');
            $tableHasUpdatedAt = Schema::hasColumn($tableName, 'updated_at');
            $tableHasTimestamps = $tableHasCreatedAt && $tableHasUpdatedAt;

            $hasIssue = $modelUsesTimestamps !== $tableHasTimestamps;

            $issue = null;
            if ($hasIssue) {
                if ($modelUsesTimestamps && !$tableHasTimestamps) {
                    $issue = 'Model uses timestamps but table lacks timestamp columns';
                } elseif (!$modelUsesTimestamps && $tableHasTimestamps) {
                    $issue = 'Table has timestamp columns but model disables timestamps';
                }
            }

            return [
                'modelName' => $modelName,
                'tableName' => $tableName,
                'hasIssue' => $hasIssue,
                'issue' => $issue,
                'modelUsesTimestamps' => $modelUsesTimestamps,
                'tableHasTimestamps' => $tableHasTimestamps,
                'tableHasCreatedAt' => $tableHasCreatedAt,
                'tableHasUpdatedAt' => $tableHasUpdatedAt
            ];

        } catch (\Exception $e) {
            return [
                'modelName' => class_basename($modelClass),
                'tableName' => 'unknown',
                'hasIssue' => true,
                'issue' => 'Error: ' . $e->getMessage(),
                'modelUsesTimestamps' => null,
                'tableHasTimestamps' => false
            ];
        }
    }

    private function fixModel(array $result): bool
    {
        if (!$result['hasIssue'] || !$result['tableHasTimestamps']) {
            return false;
        }

        $modelFile = app_path("Models/{$result['modelName']}.php");
        if (!File::exists($modelFile)) {
            return false;
        }

        $content = File::get($modelFile);
        
        // Si le modèle a explicitement désactivé les timestamps, on les active
        if (str_contains($content, 'public $timestamps = false')) {
            $newContent = str_replace(
                'public $timestamps = false;',
                '// Timestamps activés car la table a les colonnes created_at et updated_at' . PHP_EOL . '    public $timestamps = true;',
                $content
            );
            
            File::put($modelFile, $newContent);
            return true;
        }

        return false;
    }

    private function displayResults(array $issues, int $fixed): void
    {
        $this->newLine();
        
        if (empty($issues)) {
            $this->info('🎉 Tous les modèles ont une configuration timestamps cohérente !');
            return;
        }

        $this->error("❌ {count($issues)} problème(s) détecté(s) :");
        
        $this->table(
            ['Modèle', 'Table', 'Modèle Timestamps', 'Table Timestamps', 'Problème'],
            array_map(function ($issue) {
                return [
                    $issue['modelName'],
                    $issue['tableName'],
                    $issue['modelUsesTimestamps'] ? '✅' : '❌',
                    $issue['tableHasTimestamps'] ? '✅' : '❌',
                    $issue['issue']
                ];
            }, $issues)
        );

        if ($this->option('fix')) {
            $this->info("🔧 {$fixed} modèle(s) corrigé(s) automatiquement");
            
            if ($fixed > 0) {
                $this->warn('⚠️  Pensez à vérifier les changements et tester votre application');
            }
        } else {
            $this->info('💡 Utilisez --fix pour corriger automatiquement les problèmes');
        }
    }
}
