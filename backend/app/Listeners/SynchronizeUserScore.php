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
    }

    /**
     * Mettre à jour le rang d'un utilisateur en fonction de ses points totaux
     */
    private function updateUserRank($userId): void
    {
        // Calculer le total des points de l'utilisateur
        $totalPoints = Score::where('user_id', $userId)
            ->sum(DB::raw('total_points + bonus_points'));

        // Trouver le rang approprié
        $newRank = Rank::where('min_points', '<=', $totalPoints)
            ->where(function($query) use ($totalPoints) {
                $query->where('max_points', '>=', $totalPoints)
                      ->orWhereNull('max_points');
            })
            ->orderBy('min_points', 'desc')
            ->first();

        if ($newRank) {
            // Mettre à jour le rang de l'utilisateur
            \App\Models\User::where('id', $userId)
                ->update(['rank_id' => $newRank->id]);

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
