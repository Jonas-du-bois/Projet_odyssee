<?php

namespace App\Http\Controllers;

use App\Models\Weekly;
use App\Models\WeeklySeries;
use App\Models\LotteryTicket;
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
     * Lister les défis hebdomadaires actifs
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Défi de la semaine",
     *       "description": "Complétez 3 quiz cette semaine",
     *       "start_date": "2024-01-01",
     *       "end_date": "2024-01-07",
     *       "is_active": true,
     *       "ticket_reward": 5,
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Récupérer les weeklies actifs cette semaine
            $weeklies = Weekly::where('is_active', true)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $weeklies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des défis hebdomadaires',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Réclamer un ticket après un défi hebdomadaire réussi
     *
     * @urlParam id int required L'ID du défi hebdomadaire. Example: 1
     * @bodyParam quiz_count int Le nombre de quiz complétés. Example: 3
     * @bodyParam score int Le score obtenu. Example: 85
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Ticket réclamé avec succès",
     *   "data": {
     *     "tickets_earned": 5,
     *     "total_tickets": 25
     *   }
     * }
     *
     * @response 400 {
     *   "success": false,
     *   "message": "Défi non éligible ou déjà réclamé"
     * }
     *
     * @param Request $request Données de la requête
     * @param int $id Identifiant du défi hebdomadaire
     * @return JsonResponse
     */
    public function claimTicket(Request $request, $id): JsonResponse
    {
        try {
            $weekly = Weekly::findOrFail($id);
            
            // Vérifier si le weekly est actif
            if (!$weekly->is_active || $weekly->start_date > now() || $weekly->end_date < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce défi hebdomadaire n\'est pas disponible actuellement'
                ], 400);
            }
            
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
            
            // Créer le ticket
            $ticket = LotteryTicket::create([
                'user_id' => Auth::id(),
                'weekly_id' => $id,
                'claimed_at' => now(),
                'ticket_number' => $this->generateTicketNumber(),
            ]);
            
            // Mettre à jour la série de l'utilisateur
            $this->updateWeeklySeries(Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Ticket obtenu avec succès',
                'data' => $ticket
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
     * @return JsonResponse
     */
    public function getSeries(): JsonResponse
    {
        try {
            $series = WeeklySeries::where('user_id', Auth::id())
                ->first();
                
            if (!$series) {
                $series = WeeklySeries::create([
                    'user_id' => Auth::id(),
                    'current_streak' => 0,
                    'max_streak' => 0,
                    'last_weekly_date' => null,
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
     * Créer un nouveau défi hebdomadaire (admin)
     *
     * @param Request $request Données de la requête
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean',
                'reward_points' => 'integer',
                'image' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $weekly = Weekly::create($request->all());
            
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
     * @param Request $request Données de la requête
     * @param int $id Identifiant du défi hebdomadaire
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $weekly = Weekly::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'string',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'is_active' => 'boolean',
                'reward_points' => 'integer',
                'image' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $weekly->update($request->all());
            
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
     * Générer un numéro de ticket unique
     *
     * @return string
     */
    private function generateTicketNumber(): string
    {
        return strtoupper(uniqid('BL-'));
    }

    /**
     * Mettre à jour la série hebdomadaire d'un utilisateur
     *
     * @param int $userId Identifiant de l'utilisateur
     * @return void
     */
    private function updateWeeklySeries($userId): void
    {
        $series = WeeklySeries::where('user_id', $userId)->first();
        
        if (!$series) {
            $series = WeeklySeries::create([
                'user_id' => $userId,
                'current_streak' => 1,
                'max_streak' => 1,
                'last_weekly_date' => now(),
            ]);
            return;
        }
        
        $lastDate = $series->last_weekly_date ? Carbon::parse($series->last_weekly_date) : null;
        $today = Carbon::now();
        
        // Si c'est un nouveau weekly dans la même semaine, on ne change pas la streak
        if ($lastDate && $lastDate->isCurrentWeek()) {
            $series->update([
                'last_weekly_date' => now(),
            ]);
            return;
        }
        
        // Si c'est une semaine consécutive, on incrémente la streak
        if ($lastDate && $lastDate->isLastWeek()) {
            $series->current_streak++;
            $series->max_streak = max($series->max_streak, $series->current_streak);
        } else {
            // Si ce n'est pas une semaine consécutive, on réinitialise la streak
            $series->current_streak = 1;
        }
        
        $series->last_weekly_date = now();
        $series->save();
    }
}
