<?php

namespace App\Http\Controllers;

use App\Models\Novelty;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @group Novelties
 *
 * API pour gérer les nouveautés de chapitres Breitling League
 * Les nouveautés sont similaires aux découvertes mais offrent un bonus si réalisées dans les 7 jours suivant leur publication.
 */
class NoveltyController extends Controller
{
    /**
     * Lister toutes les nouveautés accessibles
     *
     * Récupère les nouveautés disponibles avec informations sur les bonus et chapitres associés
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "chapter_id": 1,
     *       "date_publication": "2025-06-01",
     *       "bonus_initial": true,
     *       "is_accessible": true,
     *       "is_bonus_eligible": true,
     *       "remaining_bonus_days": 4,
     *       "chapter": {
     *         "id": 1,
     *         "titre": "Introduction aux montres Breitling",
     *         "description": "Découvrez l'histoire et les valeurs de Breitling"
     *       },
     *       "units_count": 5
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Récupérer les nouveautés accessibles avec leurs chapitres
            $novelties = Novelty::accessible()
                ->with(['chapter'])
                ->get()
                ->map(function ($novelty) {
                    $unitsCount = $novelty->chapter ? $novelty->chapter->units()->count() : 0;
                    
                    return [
                        'id' => $novelty->id,
                        'chapter_id' => $novelty->chapter_id,
                        'date_publication' => Carbon::parse($novelty->date_publication)->format('Y-m-d'),
                        'bonus_initial' => $novelty->bonus_initial,
                        'is_accessible' => $novelty->isAccessible(),
                        'is_bonus_eligible' => $novelty->isEligibleForBonus(),                        'remaining_bonus_days' => $novelty->getRemainingBonusDays(),
                        'chapter' => $novelty->chapter ? [
                            'id' => $novelty->chapter->id,
                            'title' => $novelty->chapter->title,
                            'description' => $novelty->chapter->description
                        ] : null,
                        'units_count' => $unitsCount
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $novelties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des nouveautés',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une nouveauté spécifique avec le contenu du chapitre
     *
     * @urlParam id int required ID de la nouveauté. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "date_publication": "2025-06-01",
     *     "bonus_initial": true,
     *     "is_accessible": true,
     *     "is_bonus_eligible": true,
     *     "remaining_bonus_days": 4,
     *     "chapter": {
     *       "id": 1,
     *       "titre": "Introduction aux montres Breitling",
     *       "description": "Découvrez l'histoire et les valeurs de Breitling"
     *     },
     *     "units": [
     *       {
     *         "id": 1,
     *         "chapter_id": 1,
     *         "titre": "Histoire de Breitling",
     *         "description": "Les origines de la manufacture",
     *         "theorie_html": "<h2>Histoire de Breitling</h2><p>Contenu théorique...</p>"
     *       }
     *     ]
     *   }
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $novelty = Novelty::with(['chapter'])->findOrFail($id);
            
            // Vérifier si accessible
            if (!$novelty->isAccessible()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette nouveauté n\'est pas encore accessible',
                    'available_at' => Carbon::parse($novelty->date_publication)->format('Y-m-d')
                ], 403);
            }
            
            // Récupérer les unités du chapitre avec contenu théorique
            $units = $novelty->getChapterUnitsWithTheory();
            
            $data = [
                'id' => $novelty->id,
                'chapter_id' => $novelty->chapter_id,
                'date_publication' => Carbon::parse($novelty->date_publication)->format('Y-m-d'),
                'bonus_initial' => $novelty->bonus_initial,
                'is_accessible' => $novelty->isAccessible(),
                'is_bonus_eligible' => $novelty->isEligibleForBonus(),                'remaining_bonus_days' => $novelty->getRemainingBonusDays(),
                'chapter' => $novelty->chapter ? [
                    'id' => $novelty->chapter->id,
                    'title' => $novelty->chapter->title,
                    'description' => $novelty->chapter->description
                ] : null,
                'units' => $units
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la nouveauté',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle nouveauté (Admin)
     *
     * @bodyParam chapter_id int required ID du chapitre associé. Example: 1
     * @bodyParam date_publication date required Date de publication. Example: 2025-06-05
     * @bodyParam bonus_initial boolean Bonus accordé dans les 7 jours. Example: true
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nouveauté créée avec succès",
     *   "data": {
     *     "id": 2,
     *     "chapter_id": 1,
     *     "date_publication": "2025-06-05",
     *     "bonus_initial": true
     *   }
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'chapter_id' => 'required|integer|exists:chapters,id',
                'date_publication' => 'required|date',
                'bonus_initial' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $novelty = Novelty::create([
                'chapter_id' => $request->chapter_id,
                'date_publication' => $request->date_publication,
                'bonus_initial' => $request->bonus_initial ?? false
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Nouveauté créée avec succès',
                'data' => $novelty
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la nouveauté',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une nouveauté existante (Admin)
     *
     * @urlParam id int required ID de la nouveauté. Example: 1
     * @bodyParam chapter_id int ID du chapitre associé. Example: 1
     * @bodyParam date_publication date Date de publication. Example: 2025-06-05
     * @bodyParam bonus_initial boolean Bonus accordé dans les 7 jours. Example: true
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nouveauté mise à jour avec succès",
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "date_publication": "2025-06-05",
     *     "bonus_initial": true
     *   }
     * }
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $novelty = Novelty::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'chapter_id' => 'integer|exists:chapters,id',
                'date_publication' => 'date',
                'bonus_initial' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = array_filter([
                'chapter_id' => $request->chapter_id,
                'date_publication' => $request->date_publication,
                'bonus_initial' => $request->bonus_initial
            ], function($value) {
                return $value !== null;
            });

            $novelty->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Nouveauté mise à jour avec succès',
                'data' => $novelty->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la nouveauté',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une nouveauté (Admin)
     *
     * @urlParam id int required ID de la nouveauté. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Nouveauté supprimée avec succès"
     * }
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $novelty = Novelty::findOrFail($id);
            $novelty->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Nouveauté supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la nouveauté',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
