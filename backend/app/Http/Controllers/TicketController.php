<?php

namespace App\Http\Controllers;

use App\Models\LotteryTicket;
use App\Models\WeeklySeries;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @group Lottery Tickets
 *
 * API pour gérer les tickets de loterie et les bonus de séries
 */
class TicketController extends Controller
{
    /**
     * Lister tous les tickets de l'utilisateur connecté
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "weekly_id": 5,
     *       "tickets_earned": 3,
     *       "claimed_at": "2024-01-15T10:00:00.000000Z",
     *       "weekly": {
     *         "id": 5,
     *         "title": "Défi de la semaine 5",
     *         "start_date": "2024-01-15",
     *         "end_date": "2024-01-21"
     *       }
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function listTickets(): JsonResponse
    {        try {
            $tickets = LotteryTicket::where('user_id', Auth::id())
                ->with('weekly')
                ->orderBy('obtained_date', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $tickets
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des tickets',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Réclamer un bonus pour série de 5 tickets consécutifs
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Bonus réclamé avec succès",
     *   "data": {
     *     "bonus_tickets": 10,
     *     "series_completed": 1,
     *     "total_tickets": 35
     *   }
     * }
     *
     * @response 400 {
     *   "success": false,
     *   "message": "Aucune série complète disponible ou bonus déjà réclamé"
     * }
     *
     * @return JsonResponse
     */
    public function claimBonus(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Vérifier si l'utilisateur a une série valide
            $series = WeeklySeries::where('user_id', $userId)->first();
            
            if (!$series) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune série trouvée pour cet utilisateur'
                ], 404);
            }
              // Vérifier si la série est suffisante pour un bonus (5 semaines consécutives)
            if ($series->count < 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Série insuffisante pour réclamer un bonus',
                    'current_streak' => $series->count,
                    'required_streak' => 5
                ], 400);
            }
            
            // Calculer combien de bonus l'utilisateur peut réclamer
            $eligibleBonuses = floor($series->count / 5);
            $claimedBonuses = DB::table('lottery_tickets')
                ->where('user_id', $userId)
                ->where('bonus', true)
                ->count();
            
            $availableBonuses = $eligibleBonuses - $claimedBonuses;
            
            if ($availableBonuses <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tous les bonus ont déjà été réclamés',
                    'current_streak' => $series->count,
                    'claimed_bonuses' => $claimedBonuses
                ], 400);
            }
            
            // Créer un ticket bonus
            $bonusTicket = LotteryTicket::create([
                'user_id' => $userId,
                'weekly_id' => null, // Les tickets bonus n'ont pas de weekly_id
                'obtained_date' => now()->toDateString(),
                'bonus' => true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Bonus réclamé avec succès',
                'data' => $bonusTicket,
                'remaining_bonuses' => $availableBonuses - 1
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réclamation du bonus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir des statistiques sur les tickets
     *
     * @return JsonResponse
     */    public function getStats(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Statistiques globales
            $totalTickets = LotteryTicket::where('user_id', $userId)->count();
            $bonusTickets = LotteryTicket::where('user_id', $userId)
                ->where('bonus', true)
                ->count();
            $regularTickets = $totalTickets - $bonusTickets;
            
            // Statistiques par mois (année courante)
            $currentYear = Carbon::now()->year;
            $monthlyStats = [];
            
            for ($month = 1; $month <= 12; $month++) {
                $monthlyCount = LotteryTicket::where('user_id', $userId)
                    ->whereYear('obtained_date', $currentYear)
                    ->whereMonth('obtained_date', $month)
                    ->count();
                    
                $monthlyStats[] = [
                    'month' => Carbon::create($currentYear, $month, 1)->format('M'),
                    'count' => $monthlyCount
                ];
            }
            
            // Série actuelle et dernière date de ticket
            $series = WeeklySeries::where('user_id', $userId)->first();
            $lastTicket = LotteryTicket::where('user_id', $userId)
                ->orderBy('obtained_date', 'desc')
                ->first();
            
            $stats = [
                'total_tickets' => $totalTickets,
                'regular_tickets' => $regularTickets,
                'bonus_tickets' => $bonusTickets,
                'monthly_stats' => $monthlyStats,
                'current_streak' => $series ? $series->count : null,
                'max_streak' => $series ? $series->count : null, // Pour l'instant, on utilise count comme max
                'last_ticket_date' => $lastTicket 
                    ? Carbon::parse($lastTicket->obtained_date)->format('Y-m-d')
                    : null
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Générer un numéro de ticket bonus unique
     *
     * @return string
     */
    private function generateBonusTicketNumber(): string
    {
        return strtoupper('BLBONUS-' . uniqid());
    }
}
