<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HerokuProductionSeeder extends Seeder
{
    /**
     * Seeder optimisé pour la production Heroku
     * Inclut les données de base nécessaires pour le fonctionnement
     */
    public function run(): void
    {
        Log::info('Début du seeding pour production Heroku');

        // Désactiver temporairement les événements pour éviter les conflits durant le seeding
        $this->disableEvents();        try {
            // Seeder les données de base dans l'ordre des dépendances
            $this->call([
                // Tables indépendantes en premier
                RankSeeder::class,
                QuizTypeSeeder::class,
                ChapterSeeder::class,
                
                // Tables avec dépendances
                UserSeeder::class, // Utilisateurs essentiels pour le système
                UnitSeeder::class, // Unités de formation
                QuestionSeeder::class, // Questions pour les quiz
                ChoiceSeeder::class, // Choix des questions
                
                // Données essentielles de production
                DiscoverySeeder::class, // Découvertes disponibles
                WeeklySeeder::class, // Quiz hebdomadaires
            ]);

            // Optimiser les index après le seeding
            $this->optimizeDatabase();

            Log::info('Seeding pour production Heroku terminé avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors du seeding Heroku: ' . $e->getMessage());
            throw $e;
        } finally {
            // Réactiver les événements
            $this->enableEvents();
        }
    }

    /**
     * Désactiver temporairement les événements durant le seeding
     */
    private function disableEvents(): void
    {
        // Éviter les event listeners pendant le seeding pour les performances
        \Illuminate\Support\Facades\Event::fake([
            \App\Events\QuizCompleted::class,
            \App\Events\RankUpdated::class,
        ]);
    }

    /**
     * Réactiver les événements après le seeding
     */
    private function enableEvents(): void
    {
        // Les événements seront automatiquement réactivés après le fake
    }

    /**
     * Optimiser la base de données PostgreSQL après le seeding
     */
    private function optimizeDatabase(): void
    {
        if (config('database.default') === 'pgsql') {
            Log::info('Optimisation PostgreSQL en cours...');
            
            try {
                // ANALYZE pour mettre à jour les statistiques PostgreSQL
                DB::statement('ANALYZE;');
                
                // VACUUM pour nettoyer et optimiser
                DB::statement('VACUUM;');
                
                Log::info('Optimisation PostgreSQL terminée');
            } catch (\Exception $e) {
                Log::warning('Impossible d\'optimiser PostgreSQL: ' . $e->getMessage());
            }
        }
    }
}
