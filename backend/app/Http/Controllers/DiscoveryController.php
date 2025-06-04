<?php

namespace App\Http\Controllers;

use App\Models\Discovery;
use App\Models\Chapter;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @group Discoveries
 *
 * API pour gérer les explorations de chapitres (théorie + quiz)
 */
class DiscoveryController extends Controller
{
    /**
     * Lister les explorations de chapitres disponibles
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "chapter_id": 1,
     *       "date_disponible": "2025-06-01",
     *       "chapter": {
     *         "id": 1,
     *         "titre": "Introduction",
     *         "description": "Chapitre d'introduction"
     *       },
     *       "units_count": 5,
     *       "is_available": true
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $currentDate = Carbon::now()->toDateString();
            
            // Récupérer les discoveries avec leurs chapitres et compter les unités
            $discoveries = Discovery::with(['chapter'])
                ->get()
                ->map(function ($discovery) use ($currentDate) {
                    $unitsCount = Unit::where('chapter_id', $discovery->chapter_id)->count();
                    
                    return [
                        'id' => $discovery->id,
                        'chapter_id' => $discovery->chapter_id,
                        'date_disponible' => $discovery->date_disponible,
                        'chapter' => [
                            'id' => $discovery->chapter->id,
                            'titre' => $discovery->chapter->titre,
                            'description' => $discovery->chapter->description
                        ],
                        'units_count' => $unitsCount,
                        'is_available' => $discovery->date_disponible <= $currentDate
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $discoveries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des explorations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher une exploration de chapitre spécifique avec ses unités
     *
     * @urlParam id int required L'ID de l'exploration. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "date_disponible": "2025-06-01",
     *     "chapter": {
     *       "id": 1,
     *       "titre": "Introduction",
     *       "description": "Chapitre d'introduction"
     *     },
     *     "units": [
     *       {
     *         "id": 1,
     *         "titre": "Unité 1",
     *         "description": "Description de l'unité",
     *         "theorie_html": "<p>Contenu HTML de la théorie</p>"
     *       }
     *     ],
     *     "is_available": true
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Exploration non trouvée"
     * }
     *
     * @param int $id Identifiant de l'exploration
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $discovery = Discovery::with(['chapter'])->findOrFail($id);
            $currentDate = Carbon::now()->toDateString();
            
            // Vérifier si l'exploration est disponible
            if ($discovery->date_disponible > $currentDate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette exploration n\'est pas encore disponible',
                    'available_date' => $discovery->date_disponible
                ], 403);
            }
            
            // Récupérer les unités du chapitre avec leur théorie
            $units = Unit::where('chapter_id', $discovery->chapter_id)
                ->select(['id', 'titre', 'description', 'theorie_html'])
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $discovery->id,
                    'chapter_id' => $discovery->chapter_id,
                    'date_disponible' => $discovery->date_disponible,
                    'chapter' => [
                        'id' => $discovery->chapter->id,
                        'titre' => $discovery->chapter->titre,
                        'description' => $discovery->chapter->description
                    ],
                    'units' => $units,
                    'is_available' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Exploration non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Créer une nouvelle exploration de chapitre (admin)
     *
     * @bodyParam chapter_id int required L'ID du chapitre. Example: 1
     * @bodyParam date_disponible date required La date de disponibilité (format YYYY-MM-DD). Example: 2025-06-01
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Exploration créée avec succès",
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "date_disponible": "2025-06-01"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "errors": {
     *     "chapter_id": ["Le champ chapter_id est obligatoire."],
     *     "date_disponible": ["Le champ date_disponible est obligatoire."]
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
                'date_disponible' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier qu'il n'existe pas déjà une exploration pour ce chapitre
            $existingDiscovery = Discovery::where('chapter_id', $request->chapter_id)->first();
            if ($existingDiscovery) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une exploration existe déjà pour ce chapitre'
                ], 400);
            }

            $discovery = Discovery::create([
                'chapter_id' => $request->chapter_id,
                'date_disponible' => $request->date_disponible,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Exploration créée avec succès',
                'data' => $discovery
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'exploration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une exploration existante (admin)
     *
     * @urlParam id int required L'ID de l'exploration à modifier. Example: 1
     * @bodyParam chapter_id int L'ID du chapitre. Example: 2
     * @bodyParam date_disponible date La date de disponibilité (format YYYY-MM-DD). Example: 2025-06-15
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Exploration mise à jour avec succès",
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 2,
     *     "date_disponible": "2025-06-15"
     *   }
     * }
     *
     * @param Request $request Données de la requête
     * @param int $id Identifiant de l'exploration
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $discovery = Discovery::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'chapter_id' => 'integer|exists:chapters,id',
                'date_disponible' => 'date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Si on change le chapitre, vérifier qu'il n'y a pas de conflit
            if ($request->has('chapter_id') && $request->chapter_id != $discovery->chapter_id) {
                $existingDiscovery = Discovery::where('chapter_id', $request->chapter_id)
                    ->where('id', '!=', $id)
                    ->first();
                    
                if ($existingDiscovery) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Une exploration existe déjà pour ce chapitre'
                    ], 400);
                }
            }

            $discovery->update($request->only(['chapter_id', 'date_disponible']));
            
            return response()->json([
                'success' => true,
                'message' => 'Exploration mise à jour avec succès',
                'data' => $discovery
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'exploration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une exploration (admin)
     *
     * @urlParam id int required L'ID de l'exploration à supprimer. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Exploration supprimée avec succès"
     * }
     *
     * @param int $id Identifiant de l'exploration
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $discovery = Discovery::findOrFail($id);
            $discovery->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Exploration supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'exploration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
