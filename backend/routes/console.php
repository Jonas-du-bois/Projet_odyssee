<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programmation des tâches automatiques pour la synchronisation des scores

// 1. Synchronisation légère toutes les heures (nouveaux scores uniquement)
Schedule::command('scores:sync')
    ->hourly()
    ->withoutOverlapping() // Éviter les exécutions simultanées
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scores-sync.log')); // Log des résultats

// 2. Synchronisation complète quotidienne avec force (recalcul total)
Schedule::command('scores:sync --force')
    ->dailyAt('02:00') // 2h du matin pour éviter l'affluence
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scores-sync-daily.log'));

// 3. Synchronisation spéciale le dimanche (maintenance hebdomadaire)
Schedule::command('scores:sync --force')
    ->weeklyOn(0, '03:00') // Dimanche à 3h du matin
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/scores-sync-weekly.log'));

// 4. Commande de nettoyage des logs anciens
Schedule::command('log:clear --days=30')
    ->monthly()
    ->runInBackground();

// Commande de test pour l'événement QuizCompleted
Artisan::command('test:quiz-event', function () {
    $this->info('=== TEST DE L\'ÉVÉNEMENT QUIZCOMPLETED ===');
    
    try {
        // 1. Trouver un utilisateur et une instance de quiz
        $user = \App\Models\User::first();
        $this->info("Utilisateur sélectionné : {$user->name} (ID: {$user->id})");
        
        $quizInstance = \App\Models\QuizInstance::where('user_id', $user->id)->first();
        if (!$quizInstance) {
            $this->info('Aucune instance de quiz trouvée, création d\'une nouvelle...');
            $quizInstance = \App\Models\QuizInstance::create([
                'user_id' => $user->id,
                'quiz_type_id' => 1,
                'start_time' => now(),
                'end_time' => now()->addMinutes(10),
                'score' => 0,
                'is_completed' => false
            ]);
        }
        $this->info("Instance de quiz : ID {$quizInstance->id}");
        
        // 2. Compter les jobs avant
        $jobsCountBefore = \Illuminate\Support\Facades\DB::table('jobs')->count();
        $this->info("Jobs en file d'attente avant : $jobsCountBefore");
        
        // 3. Créer un nouveau score de quiz
        $this->info('Création d\'un nouveau UserQuizScore...');
        $newScore = \App\Models\UserQuizScore::create([
            'quiz_instance_id' => $quizInstance->id,
            'total_points' => 2000,
            'total_time' => 120, // Temps en secondes
            'ticket_obtained' => false,
            'bonus_obtained' => true
        ]);
        
        $this->info("✅ Score créé avec succès : ID {$newScore->id}");
        $this->info("Points : {$newScore->total_points}");
        
        // 4. Compter les jobs après
        $jobsCountAfter = \Illuminate\Support\Facades\DB::table('jobs')->count();
        $this->info("Jobs en file d'attente après : $jobsCountAfter");
        
        if ($jobsCountAfter > $jobsCountBefore) {
            $this->info('🎉 L\'événement QuizCompleted a été déclenché et un job est en file d\'attente !');
        } else {
            $this->warn('⚠️  Aucun nouveau job en file d\'attente. L\'événement peut ne pas s\'être déclenché.');
        }
        
        $this->info('=== TEST TERMINÉ ===');
        
    } catch (Exception $e) {
        $this->error("❌ ERREUR : " . $e->getMessage());
    }
})->purpose('Tester le déclenchement de l\'événement QuizCompleted');

// Commande pour vérifier les scores après synchronisation
Artisan::command('test:check-scores {user_id?}', function () {
    $userId = $this->argument('user_id') ?? 1;
    $this->info('=== VÉRIFICATION DES SCORES ===');
    
    $user = \App\Models\User::find($userId);
    if (!$user) {
        $this->error("Utilisateur $userId non trouvé");
        return;
    }
    
    $this->info("Utilisateur : {$user->name} (ID: {$user->id})");
    
    $score = $user->userScore;
    if ($score) {
        $this->info("Score total : {$score->total_points}");
        $this->info("Score bonus : {$score->bonus_points}");
        $this->info("Rang : " . ($score->rank_id ? $score->rank_id : 'Non défini'));
    } else {
        $this->warn("Aucun score synchronisé trouvé");
    }
    
    // Afficher aussi les quiz scores
    $quizScores = $user->userQuizScores;
    $this->info("Nombre de quiz scores : " . $quizScores->count());
    
    if ($quizScores->count() > 0) {
        $totalQuizPoints = $quizScores->sum('total_points');
        $this->info("Total des points de quiz : $totalQuizPoints");
    }
})->purpose('Vérifier les scores d\'un utilisateur');
