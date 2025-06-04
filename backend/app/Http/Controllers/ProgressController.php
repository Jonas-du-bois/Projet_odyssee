<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use App\Models\QuizInstance;
use App\Models\Rank;
use App\Models\Score;
use App\Models\UserQuizScore;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @group Progress Tracking
 *
 * API pour suivre la progression et les statistiques des utilisateurs
 */
class ProgressController extends Controller
{
    /**
     * Récupérer la progression globale de l'utilisateur connecté
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user_id": 1,
     *     "completed_chapters": 15,
     *     "completed_quizzes": 42,
     *     "total_points": 3250,
     *     "current_rank": {
     *       "id": 3,
     *       "name": "Expert",
     *       "min_points": 3000,
     *       "max_points": 5000
     *     },
     *     "next_rank": {
     *       "id": 4,
     *       "name": "Master",
     *       "min_points": 5000,
     *       "max_points": 10000
     *     },
     *     "progress_to_next_rank": 45,
     *     "last_activity": "2024-01-15T14:30:00.000000Z"
     *   }
     * }
     *
     * @return JsonResponse
     */
    public function getProgress(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Récupérer ou créer l'entrée de progression
            $progress = Progress::firstOrCreate(
                ['user_id' => $userId],
                [
                    'completed_chapters' => 0,
                    'completed_quizzes' => 0,
                    'total_points' => 0,
                    'last_activity' => now(),
                ]
            );
            
            // Mettre à jour les statistiques
            $completedQuizzes = QuizInstance::where('user_id', $userId)
                ->where('status', 'completed')
                ->count();
            
            $totalPoints = UserQuizScore::where('user_id', $userId)
                ->sum('score');
            
            // On pourrait ajouter d'autres points provenant d'autres activités
            $additionalPoints = DB::table('scores')
                ->where('user_id', $userId)
                ->sum('points');
            
            $totalPoints += $additionalPoints;
            
            // Calculer le pourcentage de progression global (exemple)
            $totalChapters = DB::table('chapters')->count();
            $completedChapters = DB::table('user_quiz_scores')
                ->join('quiz_instances', 'user_quiz_scores.quiz_instance_id', '=', 'quiz_instances.id')
                ->where('user_quiz_scores.user_id', $userId)
                ->where('user_quiz_scores.percentage', '>=', 70) // Seuil de réussite
                ->distinct('quiz_instances.chapter_id')
                ->count('quiz_instances.chapter_id');
                
            $progressPercentage = $totalChapters > 0 
                ? min(100, round(($completedChapters / $totalChapters) * 100)) 
                : 0;
            
            // Mettre à jour les données de progression
            $progress->update([
                'completed_chapters' => $completedChapters,
                'completed_quizzes' => $completedQuizzes,
                'total_points' => $totalPoints,
                'percentage' => $progressPercentage,
                'last_activity' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'progress' => $progress,
                    'total_chapters' => $totalChapters,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la progression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer le rang de l'utilisateur et ses statistiques
     *
     * @return JsonResponse
     */
    public function getRang(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Récupérer le score total de l'utilisateur
            $totalPoints = Score::where('user_id', $userId)->sum('points');
            
            // Déterminer le rang en fonction des points
            $rank = Rank::where('min_points', '<=', $totalPoints)
                ->where('max_points', '>=', $totalPoints)
                ->first();
            
            if (!$rank) {
                // Rang par défaut si aucun ne correspond
                $rank = Rank::where('min_points', 0)->first();
            }
            
            // Statistiques additionnelles
            $completedQuizzes = QuizInstance::where('user_id', $userId)
                ->where('status', 'completed')
                ->count();
            
            $averageScore = UserQuizScore::where('user_id', $userId)
                ->avg('percentage');
            
            // Position dans le classement global
            $userPosition = DB::table('scores')
                ->select('user_id')
                ->groupBy('user_id')
                ->orderByRaw('SUM(points) DESC')
                ->get()
                ->search(function ($item) use ($userId) {
                    return $item->user_id === $userId;
                }) + 1; // +1 car search renvoie l'index qui commence à 0
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rank' => $rank,
                    'total_points' => $totalPoints,
                    'completed_quizzes' => $completedQuizzes,
                    'average_score' => round($averageScore, 2),
                    'global_position' => $userPosition,
                    'next_rank' => $this->getNextRank($rank, $totalPoints)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer l'historique des quiz de l'utilisateur
     *
     * @return JsonResponse
     */
    public function getUserQuizHistory(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            $quizHistory = QuizInstance::with(['userQuizScore', 'quizType'])
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->orderBy('completed_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $quizHistory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'historique',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les données de bilan (wrap) de l'utilisateur
     *
     * @return JsonResponse
     */
    public function getWrapData(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $currentYear = Carbon::now()->year;
            
            // Statistiques annuelles
            $yearData = [
                'total_points' => Score::where('user_id', $userId)
                    ->whereYear('created_at', $currentYear)
                    ->sum('points'),
                'completed_quizzes' => QuizInstance::where('user_id', $userId)
                    ->where('status', 'completed')
                    ->whereYear('completed_at', $currentYear)
                    ->count(),
                'average_score' => UserQuizScore::where('user_id', $userId)
                    ->whereYear('created_at', $currentYear)
                    ->avg('percentage'),
                'weekly_participations' => DB::table('lottery_tickets')
                    ->where('user_id', $userId)
                    ->whereYear('claimed_at', $currentYear)
                    ->count(),
            ];
            
            // Progression par mois
            $monthlyProgress = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthlyProgress[] = [
                    'month' => Carbon::createFromDate($currentYear, $month, 1)->format('M'),
                    'points' => Score::where('user_id', $userId)
                        ->whereYear('created_at', $currentYear)
                        ->whereMonth('created_at', $month)
                        ->sum('points'),
                    'quizzes' => QuizInstance::where('user_id', $userId)
                        ->where('status', 'completed')
                        ->whereYear('completed_at', $currentYear)
                        ->whereMonth('completed_at', $month)
                        ->count(),
                ];
            }
            
            // Progression par chapitre
            $chapterProgress = DB::table('chapters')
                ->leftJoin('quiz_instances', 'chapters.id', '=', 'quiz_instances.chapter_id')
                ->leftJoin('user_quiz_scores', function($join) use ($userId) {
                    $join->on('quiz_instances.id', '=', 'user_quiz_scores.quiz_instance_id')
                        ->where('user_quiz_scores.user_id', '=', $userId);
                })
                ->select('chapters.id', 'chapters.title', 
                    DB::raw('MAX(user_quiz_scores.percentage) as best_score'),
                    DB::raw('COUNT(DISTINCT quiz_instances.id) as attempts'))
                ->groupBy('chapters.id', 'chapters.title')
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => [
                    'year_data' => $yearData,
                    'monthly_progress' => $monthlyProgress,
                    'chapter_progress' => $chapterProgress,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données de bilan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer le prochain rang à atteindre
     *
     * @param Rank $currentRank Rang actuel de l'utilisateur
     * @param int $totalPoints Points actuels de l'utilisateur
     * @return array|null
     */
    private function getNextRank($currentRank, $totalPoints): ?array
    {
        $nextRank = Rank::where('min_points', '>', $currentRank->max_points)
            ->orderBy('min_points', 'asc')
            ->first();
            
        if ($nextRank) {
            $pointsNeeded = $nextRank->min_points - $totalPoints;
            return [
                'rank' => $nextRank,
                'points_needed' => $pointsNeeded
            ];
        }
        
        return null;
    }

    /**
     * Afficher le classement général de tous les joueurs
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "user": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "rank": {"name": "Bronze", "level": 1}
     *       },
     *       "total_points": 1250,
     *       "position": 1
     *     }
     *   ]
     * }
     */
    public function getLeaderboard(): JsonResponse
    {
        try {
            $leaderboard = Score::with(['user.rank'])
                ->orderBy('points_total', 'desc')
                ->limit(50) // Top 50
                ->get()
                ->map(function ($score, $index) {
                    return [
                        'position' => $index + 1,
                        'user' => [
                            'id' => $score->user->id,
                            'name' => $score->user->name,
                            'rank' => $score->user->rank ? [
                                'name' => $score->user->rank->name,
                                'level' => $score->user->rank->level
                            ] : null
                        ],
                        'total_points' => $score->points_total,
                        'bonus_points' => $score->points_bonus
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $leaderboard
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du classement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
