<?php

namespace App\Http\Controllers;

use App\Models\Weekly;
use App\Models\WeeklySeries;
use App\Models\LotteryTicket;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @group Weekly Challenges
 *
 * API pour gérer les défis hebdomadaires et les tickets de loterie
 */
class WeeklyController extends Controller
{
    /**
     * Lister les défis hebdomadaires disponibles pour l'utilisateur
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "chapter_id": 1,
     *       "semaine": "2025-06-02",
     *       "nb_questions": 10,
     *       "chapter": {
     *         "id": 1,
     *         "titre": "Introduction",
     *         "description": "Chapitre d'introduction"
     *       },
     *       "user_has_ticket": false
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $currentWeek = Carbon::now()->startOfWeek();
            
            // Récupérer les weeklies de la semaine courante avec leurs chapitres
            $weeklies = Weekly::with('chapter')
                ->where('semaine', '>=', $currentWeek)
                ->where('semaine', '<', $currentWeek->copy()->addWeek())
                ->get();
            
            // Vérifier si l'utilisateur a déjà obtenu un ticket pour chaque weekly
            $weekliesWithTicketStatus = $weeklies->map(function ($weekly) {
                $hasTicket = LotteryTicket::where('user_id', Auth::id())
                    ->where('weekly_id', $weekly->id)
                    ->exists();
                
                return [
                    'id' => $weekly->id,
                    'chapter_id' => $weekly->chapter_id,
                    'semaine' => $weekly->semaine,
                    'nb_questions' => $weekly->nb_questions,
                    'chapter' => [
                        'id' => $weekly->chapter->id,
                        'titre' => $weekly->chapter->titre,
                        'description' => $weekly->chapter->description
                    ],
                    'user_has_ticket' => $hasTicket
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $weekliesWithTicketStatus
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des défis hebdomadaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réclamer un ticket après avoir réussi un défi hebdomadaire
     *
     * @urlParam id int required L'ID du défi hebdomadaire. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ticket réclamé avec succès",
     *   "data": {
     *     "ticket": {
     *       "id": 1,
     *       "user_id": 1,
     *       "weekly_id": 1,
     *       "date_obtenue": "2025-06-04",
     *       "bonus": false
     *     },
     *     "bonus_ticket": null,
     *     "series_count": 3
     *   }
     * }
     *
     * @response 400 {
     *   "success": false,
     *   "message": "Ticket déjà réclamé pour ce défi"
     * }
     *
     * @param int $id Identifiant du défi hebdomadaire
     * @return JsonResponse
     */
    public function claimTicket($id): JsonResponse
    {
        try {
            $weekly = Weekly::findOrFail($id);
            
            // Vérifier si le user a déjà obtenu un ticket pour ce weekly
            $existingTicket = LotteryTicket::where('user_id', Auth::id())
                ->where('weekly_id', $id)
                ->first();
                
            if ($existingTicket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà obtenu un ticket pour ce défi'
                ], 400);
            }
            
            // Créer le ticket principal
            $ticket = LotteryTicket::create([
                'user_id' => Auth::id(),
                'weekly_id' => $id,
                'date_obtenue' => now()->toDateString(),
                'bonus' => false,
            ]);
            
            // Mettre à jour la série de l'utilisateur et vérifier le bonus
            $bonusTicket = $this->updateWeeklySeries(Auth::id(), $id);
            
            // Récupérer le nombre de série actuel
            $series = WeeklySeries::where('user_id', Auth::id())->first();
            $seriesCount = $series ? $series->count : 1;
            
            return response()->json([
                'success' => true,
                'message' => 'Ticket obtenu avec succès',
                'data' => [
                    'ticket' => $ticket,
                    'bonus_ticket' => $bonusTicket,
                    'series_count' => $seriesCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réclamation du ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les informations sur la série de l'utilisateur
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "user_id": 1,
     *     "count": 3,
     *     "bonus_tickets": 0,
     *     "derniere_participation": "2025-06-04"
     *   }
     * }
     *
     * @return JsonResponse
     */
    public function getSeries(): JsonResponse
    {
        try {
            $series = WeeklySeries::where('user_id', Auth::id())->first();
                
            if (!$series) {
                $series = WeeklySeries::create([
                    'user_id' => Auth::id(),
                    'count' => 0,
                    'bonus_tickets' => 0,
                    'derniere_participation' => null,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => $series
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la série',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les tickets de loterie de l'utilisateur
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "tickets": [
     *       {
     *         "id": 1,
     *         "weekly_id": 1,
     *         "date_obtenue": "2025-06-04",
     *         "bonus": false,
     *         "weekly": {
     *           "id": 1,
     *           "chapter": {
     *             "titre": "Introduction"
     *           }
     *         }
     *       }
     *     ],
     *     "total_tickets": 5,
     *     "bonus_tickets": 1
     *   }
     * }
     *
     * @return JsonResponse
     */
    public function getTickets(): JsonResponse
    {
        try {
            $tickets = LotteryTicket::with(['weekly.chapter'])
                ->where('user_id', Auth::id())
                ->orderBy('date_obtenue', 'desc')
                ->get();
            
            $totalTickets = $tickets->count();
            $bonusTickets = $tickets->where('bonus', true)->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'tickets' => $tickets,
                    'total_tickets' => $totalTickets,
                    'bonus_tickets' => $bonusTickets
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau défi hebdomadaire (admin)
     *
     * @bodyParam chapter_id int required L'ID du chapitre. Example: 1
     * @bodyParam semaine date required La semaine du défi (format YYYY-MM-DD). Example: 2025-06-02
     * @bodyParam nb_questions int required Le nombre de questions pour le quiz. Example: 10
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Défi hebdomadaire créé avec succès",
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "semaine": "2025-06-02",
     *     "nb_questions": 10
     *   }
     * }
     *
     * @param Request $request Données de la requête
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'chapter_id' => 'required|integer|exists:chapters,id',
                'semaine' => 'required|date',
                'nb_questions' => 'required|integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier qu'il n'existe pas déjà un weekly pour cette semaine et ce chapitre
            $existingWeekly = Weekly::where('chapter_id', $request->chapter_id)
                ->where('semaine', $request->semaine)
                ->first();
                
            if ($existingWeekly) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un défi hebdomadaire existe déjà pour ce chapitre cette semaine'
                ], 400);
            }

            $weekly = Weekly::create([
                'chapter_id' => $request->chapter_id,
                'semaine' => $request->semaine,
                'nb_questions' => $request->nb_questions,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Défi hebdomadaire créé avec succès',
                'data' => $weekly
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du défi hebdomadaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un défi hebdomadaire existant (admin)
     *
     * @bodyParam chapter_id int L'ID du chapitre. Example: 1
     * @bodyParam semaine date La semaine du défi (format YYYY-MM-DD). Example: 2025-06-02
     * @bodyParam nb_questions int Le nombre de questions pour le quiz. Example: 15
     *
     * @param Request $request Données de la requête
     * @param int $id Identifiant du défi hebdomadaire
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $weekly = Weekly::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'chapter_id' => 'integer|exists:chapters,id',
                'semaine' => 'date',
                'nb_questions' => 'integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Si on change le chapitre ou la semaine, vérifier qu'il n'y a pas de conflit
            if ($request->has('chapter_id') || $request->has('semaine')) {
                $chapterId = $request->get('chapter_id', $weekly->chapter_id);
                $semaine = $request->get('semaine', $weekly->semaine);
                
                $existingWeekly = Weekly::where('chapter_id', $chapterId)
                    ->where('semaine', $semaine)
                    ->where('id', '!=', $id)
                    ->first();
                    
                if ($existingWeekly) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un défi hebdomadaire existe déjà pour ce chapitre cette semaine'
                    ], 400);
                }
            }

            $weekly->update($request->only(['chapter_id', 'semaine', 'nb_questions']));
            
            return response()->json([
                'success' => true,
                'message' => 'Défi hebdomadaire mis à jour avec succès',
                'data' => $weekly
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du défi hebdomadaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un défi hebdomadaire (admin)
     *
     * @param int $id Identifiant du défi hebdomadaire
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $weekly = Weekly::findOrFail($id);
            $weekly->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Défi hebdomadaire supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du défi hebdomadaire',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour la série hebdomadaire d'un utilisateur
     * Retourne un ticket bonus si l'utilisateur atteint 5 weeklies d'affilée
     *
     * @param int $userId Identifiant de l'utilisateur
     * @param int $weeklyId Identifiant du weekly complété
     * @return LotteryTicket|null Ticket bonus si applicable
     */
    private function updateWeeklySeries($userId, $weeklyId): ?LotteryTicket
    {
        $series = WeeklySeries::where('user_id', $userId)->first();
        $currentDate = now()->toDateString();
        
        if (!$series) {
            // Créer une nouvelle série
            WeeklySeries::create([
                'user_id' => $userId,
                'count' => 1,
                'bonus_tickets' => 0,
                'derniere_participation' => $currentDate,
            ]);
            return null;
        }
        
        $lastParticipation = $series->derniere_participation ? Carbon::parse($series->derniere_participation) : null;
        $currentWeek = Carbon::now()->startOfWeek();
        
        // Vérifier si c'est une semaine consécutive
        if ($lastParticipation && $lastParticipation->startOfWeek()->addWeek()->equalTo($currentWeek)) {
            // Semaine consécutive : incrémenter le compteur
            $series->count++;
        } else {
            // Première participation ou série interrompue : remettre à 1
            $series->count = 1;
        }
        
        $series->derniere_participation = $currentDate;
        
        // Vérifier si l'utilisateur atteint 5 weeklies d'affilée
        $bonusTicket = null;
        if ($series->count == 5) {
            // Créer un ticket bonus
            $bonusTicket = LotteryTicket::create([
                'user_id' => $userId,
                'weekly_id' => $weeklyId,
                'date_obtenue' => $currentDate,
                'bonus' => true,
            ]);
            
            $series->bonus_tickets++;
            $series->count = 0; // Remettre le compteur à zéro après le bonus
        }
        
        $series->save();
        
        return $bonusTicket;
    }
}
