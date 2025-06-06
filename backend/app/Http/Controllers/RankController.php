<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Rangs
 * 
 * Gestion des rangs et progression des utilisateurs.
 */
class RankController extends Controller
{
    /**
     * Liste de tous les rangs
     * 
     * Retourne tous les rangs triés par niveau croissant.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Novice",
     *       "level": 1,
     *       "minimum_points": 0
     *     }
     *   ],
     *   "message": "Ranks retrieved successfully"
     * }
     */
    public function index(): JsonResponse
    {
        try {
            $ranks = Rank::orderBy('level')->get();

            return response()->json([
                'success' => true,
                'data' => $ranks,
                'message' => 'Ranks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving ranks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Détails d’un rang
     * 
     * Retourne les informations pour un rang donné par son identifiant.
     * 
     * @urlParam id int Requis. ID du rang.
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "Novice",
     *     "level": 1,
     *     "minimum_points": 0
     *   },
     *   "message": "Rank retrieved successfully"
     * }
     */
    public function show(string $id): JsonResponse    {
        try {
            $rank = Rank::findOrFail((int) $id);

            return response()->json([
                'success' => true,
                'data' => $rank,
                'message' => 'Rank retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rank not found'
            ], 404);
        }
    }

    /**
     * Rang précédent et suivant d’un utilisateur
     * 
     * Retourne les rangs adjacent au rang actuel de l'utilisateur connecté.
     * 
     * @authenticated
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "current_rank": {...},
     *     "previous_rank": {...},
     *     "next_rank": {...}
     *   },
     *   "message": "Adjacent ranks retrieved successfully"
     * }
     */    public function getAdjacentRanks(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->rank_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no rank assigned'
                ], 400);
            }

            $currentRank = Rank::find($user->rank_id);

            if (!$currentRank) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current rank not found'
                ], 404);
            }

            $previousRank = $currentRank->getPreviousRank();
            $nextRank = $currentRank->getNextRank();

            return response()->json([
                'success' => true,
                'data' => [
                    'current_rank' => $currentRank,
                    'previous_rank' => $previousRank,
                    'next_rank' => $nextRank
                ],
                'message' => 'Adjacent ranks retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving adjacent ranks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Seuils de points par rang
     * 
     * Retourne les points minimum requis pour chaque rang.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Novice",
     *       "level": 1,
     *       "minimum_points": 0,
     *       "formatted_points": "0"
     *     }
     *   ],
     *   "message": "Minimum points structure retrieved successfully"
     * }
     */
    public function getMinimumPoints(): JsonResponse
    {
        try {
            $ranks = Rank::select('id', 'name', 'level', 'minimum_points')
                         ->orderBy('level')
                         ->get();

            $pointsStructure = $ranks->map(function ($rank) {
                return [
                    'id' => $rank->id,
                    'name' => $rank->name,
                    'level' => $rank->level,
                    'minimum_points' => $rank->minimum_points,
                    'formatted_points' => number_format($rank->minimum_points)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $pointsStructure,
                'message' => 'Minimum points structure retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving minimum points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Progression de l’utilisateur
     * 
     * Calcule le rang actuel de l'utilisateur et la progression vers le suivant.
     * 
     * @authenticated
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "user_points": 320,
     *     "current_rank": {...},
     *     "previous_rank": {...},
     *     "next_rank": {...},
     *     "points_needed_for_next": 80,
     *     "progress_percentage": 80.0
     *   },
     *   "message": "User progression retrieved successfully"
     * }
     */
    public function getUserProgression(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $totalPoints = $user->scores()->sum('points');

            $currentRank = Rank::getRankByPoints($totalPoints);
            $nextRank = $currentRank ? $currentRank->getNextRank() : null;
            $previousRank = $currentRank ? $currentRank->getPreviousRank() : null;

            $progression = [
                'user_points' => $totalPoints,
                'current_rank' => $currentRank,
                'previous_rank' => $previousRank,
                'next_rank' => $nextRank,
                'points_needed_for_next' => $nextRank ? ($nextRank->minimum_points - $totalPoints) : 0,
                'progress_percentage' => $this->calculateProgressPercentage($currentRank, $nextRank, $totalPoints)
            ];

            return response()->json([
                'success' => true,
                'data' => $progression,
                'message' => 'User progression retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user progression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques globales par rang
     * 
     * Retourne le nombre d'utilisateurs par rang ainsi que leur proportion.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Novice",
     *       "level": 1,
     *       "minimum_points": 0,
     *       "users_count": 5,
     *       "percentage": 16.67
     *     }
     *   ],
     *   "message": "Rank statistics retrieved successfully"
     * }
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $ranks = Rank::withCount('users')->orderBy('level')->get();

            $statistics = $ranks->map(function ($rank) {
                return [
                    'id' => $rank->id,
                    'name' => $rank->name,
                    'level' => $rank->level,
                    'minimum_points' => $rank->minimum_points,
                    'users_count' => $rank->users_count,
                    'percentage' => $this->calculatePercentage($rank->users_count)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Rank statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving rank statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcule le pourcentage de progression entre deux rangs
     */
    private function calculateProgressPercentage($currentRank, $nextRank, int $userPoints): float
    {
        if (!$currentRank || !$nextRank) {
            return 100.0; // Rang maximum atteint
        }

        $pointsNeeded = $nextRank->minimum_points - $currentRank->minimum_points;
        $pointsEarned = $userPoints - $currentRank->minimum_points;

        return $pointsNeeded > 0
            ? round(($pointsEarned / $pointsNeeded) * 100, 2)
            : 100.0;
    }

    /**
     * Calcule la proportion d'utilisateurs dans un rang donné
     */
    private function calculatePercentage(int $usersInRank): float
    {
        $totalUsers = User::count();
        return $totalUsers > 0
            ? round(($usersInRank / $totalUsers) * 100, 2)
            : 0.0;
    }
}
