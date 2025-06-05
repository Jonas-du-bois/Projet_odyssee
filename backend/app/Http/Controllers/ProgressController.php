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
                ->whereHas('userQuizScore')
                ->count();
            
            $totalPoints = UserQuizScore::whereHas('quizInstance', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->sum('total_points');
            
            // On pourrait ajouter d'autres points provenant d'autres activités
            $additionalPoints = DB::table('scores')
                ->where('user_id', $userId)
                ->sum('total_points');
            
            $totalPoints += $additionalPoints;            // Calculer le pourcentage de progression global (exemple)
            $totalChapters = DB::table('chapters')->count();
            
            // Récupérer les chapitres complétés à partir des quiz instances
            // en utilisant les relations polymorphes avec les différents types de modules
            $completedChapterIds = collect();
            
            // Pour chaque type de module, joindre la table appropriée pour récupérer chapter_id
            $moduleTypes = [
                'Unit' => 'units',
                'Discovery' => 'discoveries', 
                'Novelty' => 'novelties',
                'Weekly' => 'weeklies',
                'Reminder' => 'reminders'
            ];
              foreach ($moduleTypes as $moduleType => $tableName) {
                $chapterIds = DB::table('user_quiz_scores')
                    ->join('quiz_instances', 'user_quiz_scores.quiz_instance_id', '=', 'quiz_instances.id')
                    ->join($tableName, function($join) use ($moduleType, $tableName) {
                        $join->on('quiz_instances.module_id', '=', $tableName . '.id')
                             ->where('quiz_instances.module_type', '=', $moduleType);
                    })
                    ->where('quiz_instances.user_id', $userId)
                    ->where('user_quiz_scores.total_points', '>=', 700) // Seuil de réussite (70% de 1000 points de base)
                    ->distinct($tableName . '.chapter_id')
                    ->pluck($tableName . '.chapter_id');
                
                $completedChapterIds = $completedChapterIds->merge($chapterIds);
            }
            
            $completedChapters = $completedChapterIds->unique()->count();
                
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
    }    /**
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
            
            // Statistiques annuelles
            $yearData = [
                'total_points' => Score::where('user_id', $userId)
                    ->whereYear('created_at', $currentYear)
                    ->sum('total_points'),
                'completed_quizzes' => QuizInstance::where('user_id', $userId)
                    ->whereHas('userQuizScore')
                    ->whereYear('created_at', $currentYear)
                    ->count(),
                'average_score' => UserQuizScore::whereHas('quizInstance', function($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                    ->whereYear('created_at', $currentYear)
                    ->avg('total_points'),
                'weekly_participations' => DB::table('lottery_tickets')
                    ->where('user_id', $userId)
                    ->whereYear('obtained_date', $currentYear)
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
                        ->sum('total_points'),
                    'quizzes' => QuizInstance::where('user_id', $userId)
                        ->whereHas('userQuizScore')
                        ->whereYear('created_at', $currentYear)
                        ->whereMonth('created_at', $month)
                        ->count(),
                ];
            }              // Progression par chapitre avec vraies statistiques
            $chapterProgress = DB::table('chapters')
                ->leftJoin('units', 'chapters.id', '=', 'units.chapter_id')
                ->leftJoin('quiz_instances as qi_units', function($join) use ($userId) {
                    $join->on('units.id', '=', 'qi_units.module_id')
                         ->where('qi_units.module_type', '=', 'Unit')
                         ->where('qi_units.user_id', '=', $userId);
                })
                ->leftJoin('user_quiz_scores as uqs_units', 'qi_units.id', '=', 'uqs_units.quiz_instance_id')
                ->leftJoin('discoveries', 'chapters.id', '=', 'discoveries.chapter_id')
                ->leftJoin('quiz_instances as qi_discoveries', function($join) use ($userId) {
                    $join->on('discoveries.id', '=', 'qi_discoveries.module_id')
                         ->where('qi_discoveries.module_type', '=', 'Discovery')
                         ->where('qi_discoveries.user_id', '=', $userId);
                })
                ->leftJoin('user_quiz_scores as uqs_discoveries', 'qi_discoveries.id', '=', 'uqs_discoveries.quiz_instance_id')
                ->leftJoin('novelties', 'chapters.id', '=', 'novelties.chapter_id')
                ->leftJoin('quiz_instances as qi_novelties', function($join) use ($userId) {
                    $join->on('novelties.id', '=', 'qi_novelties.module_id')
                         ->where('qi_novelties.module_type', '=', 'Novelty')
                         ->where('qi_novelties.user_id', '=', $userId);
                })
                ->leftJoin('user_quiz_scores as uqs_novelties', 'qi_novelties.id', '=', 'uqs_novelties.quiz_instance_id')
                ->select(
                    'chapters.id',
                    'chapters.title',
                    DB::raw('COALESCE(MAX(GREATEST(
                        COALESCE(uqs_units.total_points, 0),
                        COALESCE(uqs_discoveries.total_points, 0),
                        COALESCE(uqs_novelties.total_points, 0)
                    )), 0) as best_score'),
                    DB::raw('(
                        COALESCE(COUNT(DISTINCT qi_units.id), 0) +
                        COALESCE(COUNT(DISTINCT qi_discoveries.id), 0) +
                        COALESCE(COUNT(DISTINCT qi_novelties.id), 0)
                    ) as attempts')
                )
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
}
