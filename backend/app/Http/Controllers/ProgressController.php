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
use Illuminate\Support\Facades\Log;

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
     * }     *
     * @return JsonResponse
     */
    public function getProgress(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Calculer les statistiques globales réelles
            $completedQuizzes = QuizInstance::where('user_id', $userId)
                ->whereHas('userQuizScore')
                ->count();
            
            // Points depuis user_quiz_scores
            $quizPoints = UserQuizScore::whereHas('quizInstance', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->sum('total_points');
            
            // Points depuis la table scores (bonus, événements, etc.)
            $bonusPoints = Score::where('user_id', $userId)->sum('total_points');
            
            $totalPoints = $quizPoints + $bonusPoints;
            
            // Calculer les chapitres complétés selon la logique Breitling League
            $totalChapters = DB::table('chapters')->count();
            
            // Récupérer les chapitres complétés à partir des différents types de modules
            $completedChapterIds = collect();
            
            $moduleTypes = [
                'Discovery' => 'discoveries',
                'Novelty' => 'novelties', 
                'Weekly' => 'weeklies',
                'Reminder' => 'reminders',
                'Unit' => 'units'
            ];
            
            foreach ($moduleTypes as $moduleType => $tableName) {
                // Vérifier si la table existe avant de faire la requête
                if (DB::getSchemaBuilder()->hasTable($tableName)) {
                    $chapterIds = DB::table('user_quiz_scores')
                        ->join('quiz_instances', 'user_quiz_scores.quiz_instance_id', '=', 'quiz_instances.id')
                        ->join($tableName, function($join) use ($moduleType, $tableName) {
                            $join->on('quiz_instances.module_id', '=', $tableName . '.id')
                                 ->where('quiz_instances.module_type', '=', $moduleType);
                        })
                        ->where('quiz_instances.user_id', $userId)
                        ->where('user_quiz_scores.total_points', '>=', 700) // Seuil de réussite (70%)
                        ->distinct($tableName . '.chapter_id')
                        ->pluck($tableName . '.chapter_id');
                    
                    $completedChapterIds = $completedChapterIds->merge($chapterIds);
                }
            }
            
            $completedChapters = $completedChapterIds->unique()->count();
            $progressPercentage = $totalChapters > 0 
                ? min(100, round(($completedChapters / $totalChapters) * 100)) 
                : 0;
            
            // Récupérer le rang actuel de l'utilisateur
            $currentRank = Rank::where('minimum_points', '<=', $totalPoints)
                ->orderBy('minimum_points', 'desc')
                ->first();
            
            if (!$currentRank) {
                $currentRank = Rank::orderBy('minimum_points', 'asc')->first();
            }
            
            // Récupérer le prochain rang
            $nextRank = null;
            $pointsToNextRank = 0;
            if ($currentRank) {
                $nextRank = Rank::where('minimum_points', '>', $currentRank->minimum_points)
                    ->orderBy('minimum_points', 'asc')
                    ->first();
                    
                if ($nextRank) {
                    $pointsToNextRank = $nextRank->minimum_points - $totalPoints;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'completed_chapters' => $completedChapters,
                    'total_chapters' => $totalChapters,
                    'completed_quizzes' => $completedQuizzes,
                    'total_points' => $totalPoints,
                    'quiz_points' => $quizPoints,
                    'bonus_points' => $bonusPoints,
                    'progress_percentage' => $progressPercentage,
                    'current_rank' => $currentRank ? [
                        'id' => $currentRank->id,
                        'name' => $currentRank->name,
                        'level' => $currentRank->level,
                        'minimum_points' => $currentRank->minimum_points
                    ] : null,
                    'next_rank' => $nextRank ? [
                        'id' => $nextRank->id,
                        'name' => $nextRank->name,
                        'level' => $nextRank->level,
                        'minimum_points' => $nextRank->minimum_points,
                        'points_needed' => $pointsToNextRank
                    ] : null,
                    'last_activity' => now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la progression',
                'error' => $e->getMessage()
            ], 500);
        }
    }/**
     * Récupérer le rang de l'utilisateur et ses statistiques
     *
     * @return JsonResponse
     */
    public function getRang(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Récupérer le score total de l'utilisateur
            $totalPoints = Score::where('user_id', $userId)->sum('total_points');
            
            // Déterminer le rang en fonction des points
            $rank = Rank::where('minimum_points', '<=', $totalPoints)
                ->orderBy('minimum_points', 'desc')
                ->first();
            
            if (!$rank) {
                // Rang par défaut si aucun ne correspond
                $rank = Rank::orderBy('minimum_points', 'asc')->first();
            }
            
            // Statistiques additionnelles
            $completedQuizzes = QuizInstance::where('user_id', $userId)
                ->whereHas('userQuizScore')
                ->count();
            
            $averageScore = UserQuizScore::whereHas('quizInstance', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->avg('total_points');
            
            // Position dans le classement global
            $userPosition = DB::table('scores')
                ->select('user_id')
                ->groupBy('user_id')
                ->orderByRaw('SUM(total_points) DESC')
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
    }    /**
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
                ->whereHas('userQuizScore')
                ->orderBy('created_at', 'desc')
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
    }    /**
     * Récupérer les données de bilan (wrap) de l'utilisateur
     *
     * @return JsonResponse
     */
    public function getWrapData(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $currentYear = Carbon::now()->year;
            
            // Version simplifiée pour éviter les erreurs complexes
            $totalPoints = Score::where('user_id', $userId)
                ->whereYear('created_at', $currentYear)
                ->sum('total_points');
                
            $completedQuizzes = QuizInstance::where('user_id', $userId)
                ->whereHas('userQuizScore')
                ->whereYear('created_at', $currentYear)
                ->count();
                
            // Progression par mois simple
            $monthlyProgress = [];
            for ($month = 1; $month <= 12; $month++) {
                $monthPoints = Score::where('user_id', $userId)
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->sum('total_points');
                    
                $monthQuizzes = QuizInstance::where('user_id', $userId)
                    ->whereHas('userQuizScore')
                    ->whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->count();
                    
                $monthlyProgress[] = [
                    'month' => Carbon::createFromDate($currentYear, $month, 1)->format('M'),
                    'points' => $monthPoints,
                    'quizzes' => $monthQuizzes,
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'year' => $currentYear,
                    'summary' => [
                        'total_points' => $totalPoints,
                        'completed_quizzes' => $completedQuizzes,
                    ],
                    'monthly_progress' => $monthlyProgress,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Wrap data error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données de bilan: ' . $e->getMessage()
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
        $nextRank = Rank::where('minimum_points', '>', $currentRank->minimum_points)
            ->orderBy('minimum_points', 'asc')
            ->first();
            
        if ($nextRank) {
            $pointsNeeded = $nextRank->minimum_points - $totalPoints;
            return [
                'rank' => $nextRank,
                'points_needed' => $pointsNeeded
            ];
        }
        
        return null;
    }    /**
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
                ->orderBy('total_points', 'desc')
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
                        'total_points' => $score->total_points,
                        'bonus_points' => $score->bonus_points
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
    
    /**
     * Obtenir les utilisateurs autour de la position donnée
     */
    private function getUserSurrounding($userRanking, $userPosition): array
    {
        $start = max(0, $userPosition - 3);
        $end = min($userRanking->count(), $userPosition + 2);
        
        return $userRanking->slice($start, $end - $start)->values()->toArray();
    }
}
