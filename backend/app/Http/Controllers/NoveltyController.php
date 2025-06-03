<?php

namespace App\Http\Controllers;

use App\Models\Novelty;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @group Novelties
 *
 * API pour gérer les nouveautés produits Breitling
 */
class NoveltyController extends Controller
{
    /**
     * Lister toutes les nouveautés
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Nouvelle Navitimer B01",
     *       "description": "Découvrez la nouvelle collection Navitimer avec mouvement manufacture B01",
     *       "product_code": "NAV-B01-2024",
     *       "image": "https://example.com/navitimer.jpg",
     *       "start_date": "2024-01-01",
     *       "end_date": "2024-03-31",
     *       "bonus_points": 50,
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
            $novelties = Novelty::all();
            
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
    }    /**
     * Créer une nouvelle nouveauté
     *
     * @bodyParam title string required Le titre de la nouveauté. Example: Nouvelle Navitimer B01
     * @bodyParam description string required La description de la nouveauté. Example: Découvrez la nouvelle collection Navitimer avec mouvement manufacture B01
     * @bodyParam product_code string required Le code produit. Example: NAV-B01-2024
     * @bodyParam image string URL de l'image du produit. Example: https://example.com/navitimer.jpg
     * @bodyParam start_date date required Date de début de disponibilité. Example: 2024-01-01
     * @bodyParam end_date date required Date de fin de promotion. Example: 2024-03-31
     * @bodyParam bonus_points int Points bonus accordés. Example: 50
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Nouveauté créée avec succès",
     *   "data": {
     *     "id": 1,
     *     "title": "Nouvelle Navitimer B01",
     *     "description": "Découvrez la nouvelle collection Navitimer avec mouvement manufacture B01",
     *     "product_code": "NAV-B01-2024",
     *     "image": "https://example.com/navitimer.jpg",
     *     "start_date": "2024-01-01",
     *     "end_date": "2024-03-31",
     *     "bonus_points": 50,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
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
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'product_code' => 'required|string|max:50',
                'image' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'bonus_points' => 'integer',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $novelty = Novelty::create($request->all());
            
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
     * Mettre à jour une nouveauté existante
     *
     * @param Request $request Données de la requête
     * @param int $id Identifiant de la nouveauté
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $novelty = Novelty::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'string',
                'product_code' => 'string|max:50',
                'image' => 'nullable|string',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'bonus_points' => 'integer',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $novelty->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Nouveauté mise à jour avec succès',
                'data' => $novelty
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
     * Supprimer une nouveauté
     *
     * @param int $id Identifiant de la nouveauté
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
