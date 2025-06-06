<?php

namespace App\Listeners;

use App\Events\QuizCompleted;
use App\Models\Score;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class SynchronizeUserScore implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(QuizCompleted $event): void
    {
        $userQuizScore = $event->userQuizScore;
        $userId = $userQuizScore->quizInstance->user_id;
        $points = $userQuizScore->total_points;
        $bonusPoints = $userQuizScore->bonus_obtained ?? 0;        DB::transaction(function () use ($userId, $points, $bonusPoints) {
            // Chercher le score existant pour ce mois ou créer un nouveau
            $currentMonth = Carbon::now()->format('Y-m');
            
            $score = Score::where('user_id', $userId)
                ->whereRaw("strftime('%Y-%m', created_at) = ?", [$currentMonth])
                ->first();

            if ($score) {
                // Mettre à jour le score existant
                $score->increment('total_points', $points);
                if ($bonusPoints > 0) {
                    $score->increment('bonus_points', $bonusPoints);
                }
            } else {
                // Créer un nouveau score pour ce mois
                $currentRank = $this->getCurrentUserRank($userId);
                  $score = Score::create([
                    'user_id' => $userId,
                    'total_points' => $points,
                    'bonus_points' => $bonusPoints,
                    'rank_id' => $currentRank ? $currentRank->id : 1,
                ]);
            }

            // Mettre à jour le rang en fonction du total des points
            $this->updateUserRank($userId);
        });
    }

    /**
     * Obtenir le rang actuel d'un utilisateur
     */
    private function getCurrentUserRank($userId)
    {
        $user = \App\Models\User::find($userId);
        return $user ? $user->rank : null;
    }    /**
     * Mettre à jour le rang d'un utilisateur en fonction de ses points totaux
     */
    private function updateUserRank($userId): void
    {
        // Calculer le total des points de l'utilisateur
        $totalPoints = Score::where('user_id', $userId)
            ->sum(DB::raw('total_points + bonus_points'));

        // Trouver le rang approprié - le rang le plus élevé dont les points minimum sont atteints
        $newRank = Rank::where('minimum_points', '<=', $totalPoints)
            ->orderBy('minimum_points', 'desc')
            ->first();

        // Si aucun rang trouvé, prendre le rang de niveau 1 (minimum)
        if (!$newRank) {
            $newRank = Rank::orderBy('level', 'asc')->first();
        }        if ($newRank) {
            // Récupérer l'utilisateur et son rang actuel
            $user = \App\Models\User::find($userId);
            $oldRankId = $user->rank_id;

            // Mettre à jour le rang de l'utilisateur seulement si il a changé
            if ($oldRankId !== $newRank->id) {
                $user->update(['rank_id' => $newRank->id]);

                // Déclencher un event de mise à jour de rang
                event(new \App\Events\RankUpdated($user, $oldRankId, $newRank->id, $totalPoints));
            }

            // Mettre à jour le rang dans le score le plus récent
            $latestScore = Score::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($latestScore && $latestScore->rank_id !== $newRank->id) {
                $latestScore->update(['rank_id' => $newRank->id]);
            }
        }
    }
}
