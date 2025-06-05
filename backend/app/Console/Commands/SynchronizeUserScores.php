<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserQuizScore;
use App\Models\Score;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SynchronizeUserScores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scores:sync {--user-id= : ID de l\'utilisateur spécifique à synchroniser} {--force : Forcer la re-synchronisation même si des scores existent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise les scores des quiz avec la table scores et met à jour les rangs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la synchronisation des scores...');

        $userId = $this->option('user-id');
        $force = $this->option('force');

        if ($userId) {
            $this->synchronizeUser($userId, $force);
        } else {
            $this->synchronizeAllUsers($force);
        }

        $this->info('Synchronisation terminée !');
    }

    /**
     * Synchroniser tous les utilisateurs
     */
    private function synchronizeAllUsers($force = false)
    {
        $users = User::all();
        $progressBar = $this->output->createProgressBar($users->count());

        foreach ($users as $user) {
            $this->synchronizeUser($user->id, $force);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line('');
    }

    /**
     * Synchroniser un utilisateur spécifique
     */
    private function synchronizeUser($userId, $force = false)
    {
        $user = User::find($userId);
        if (!$user) {
            $this->error("Utilisateur ID $userId non trouvé");
            return;
        }

        // Vérifier si l'utilisateur a déjà des scores
        $existingScoresCount = Score::where('user_id', $userId)->count();
        
        if ($existingScoresCount > 0 && !$force) {
            $this->line("Utilisateur {$user->name} a déjà des scores. Utilisez --force pour re-synchroniser.");
            return;
        }

        if ($force) {
            // Supprimer les scores existants
            Score::where('user_id', $userId)->delete();
            $this->line("Scores existants supprimés pour {$user->name}");
        }

        // Récupérer tous les quiz scores de l'utilisateur
        $userQuizScores = UserQuizScore::whereHas('quizInstance', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('quizInstance')->get();

        if ($userQuizScores->isEmpty()) {
            $this->line("Aucun quiz score trouvé pour {$user->name}");
            return;
        }

        // Grouper par mois
        $scoresByMonth = $userQuizScores->groupBy(function($score) {
            // Utiliser la date de création du quiz instance ou une date par défaut
            $date = $score->quizInstance->created_at ?? Carbon::now();
            return Carbon::parse($date)->format('Y-m');
        });

        $totalPointsAdded = 0;
        $totalBonusAdded = 0;

        foreach ($scoresByMonth as $month => $monthScores) {
            $totalPoints = $monthScores->sum('total_points');
            $totalBonus = $monthScores->sum('bonus_obtained');
            
            // Créer le score mensuel
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            
            Score::create([
                'user_id' => $userId,
                'total_points' => $totalPoints,
                'bonus_points' => $totalBonus,
                'rank_id' => $user->rank_id ?? 1,
                'created_at' => $monthDate,
                'updated_at' => $monthDate,
            ]);

            $totalPointsAdded += $totalPoints;
            $totalBonusAdded += $totalBonus;
        }

        // Mettre à jour le rang
        $this->updateUserRank($userId);

        $this->line("✅ {$user->name}: {$totalPointsAdded} points + {$totalBonusAdded} bonus synchronisés");
    }

    /**
     * Mettre à jour le rang d'un utilisateur
     */
    private function updateUserRank($userId)
    {
        $totalPoints = Score::where('user_id', $userId)
            ->sum(DB::raw('total_points + bonus_points'));

        $newRank = Rank::where('min_points', '<=', $totalPoints)
            ->where(function($query) use ($totalPoints) {
                $query->where('max_points', '>=', $totalPoints)
                      ->orWhereNull('max_points');
            })
            ->orderBy('min_points', 'desc')
            ->first();

        if ($newRank) {
            User::where('id', $userId)->update(['rank_id' => $newRank->id]);
            
            // Mettre à jour tous les scores de cet utilisateur avec le nouveau rang
            Score::where('user_id', $userId)->update(['rank_id' => $newRank->id]);
        }
    }
}
