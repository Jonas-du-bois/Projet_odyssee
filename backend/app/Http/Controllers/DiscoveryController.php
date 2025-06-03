<?php

namespace App\Http\Controllers;

use App\Models\Discovery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @group Discoveries
 *
 * API pour gérer les découvertes de la plateforme
 */
class DiscoveryController extends Controller
{
    /**
     * Lister toutes les découvertes
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Nouvelle collection Breitling",
     *       "content": "Découvrez notre nouvelle collection...",
     *       "image": "https://example.com/image.jpg",
     *       "is_active": true,
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
            $discoveries = Discovery::all();
            
            return response()->json([
                'success' => true,
                'data' => $discoveries
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des découvertes',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Afficher une découverte spécifique
     *
     * @urlParam id int required L'ID de la découverte. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "title": "Nouvelle collection Breitling",
     *     "content": "Découvrez notre nouvelle collection...",
     *     "image": "https://example.com/image.jpg",
     *     "is_active": true,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Découverte non trouvée",
     *   "error": "No query results for model [App\\Models\\Discovery] 1"
     * }
     *
     * @param int $id Identifiant de la découverte
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $discovery = Discovery::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $discovery
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Découverte non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }    /**
     * Créer une nouvelle découverte
     *
     * @bodyParam title string required Le titre de la découverte. Example: Nouvelle collection Breitling
     * @bodyParam content string required Le contenu de la découverte. Example: Découvrez notre nouvelle collection avec des modèles innovants...
     * @bodyParam image string L'URL de l'image associée. Example: https://example.com/image.jpg
     * @bodyParam is_active boolean Statut d'activation de la découverte. Example: true
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Découverte créée avec succès",
     *   "data": {
     *     "id": 1,
     *     "title": "Nouvelle collection Breitling",
     *     "content": "Découvrez notre nouvelle collection...",
     *     "image": "https://example.com/image.jpg",
     *     "is_active": true,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "errors": {
     *     "title": ["Le champ title est obligatoire."],
     *     "content": ["Le champ content est obligatoire."]
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
                'content' => 'required|string',
                'image' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $discovery = Discovery::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Découverte créée avec succès',
                'data' => $discovery
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la découverte',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Mettre à jour une découverte existante
     *
     * @urlParam id int required L'ID de la découverte à modifier. Example: 1
     * @bodyParam title string Le titre de la découverte. Example: Nouvelle collection Breitling mise à jour
     * @bodyParam content string Le contenu de la découverte. Example: Découvrez notre collection mise à jour...
     * @bodyParam image string L'URL de l'image associée. Example: https://example.com/new-image.jpg
     * @bodyParam is_active boolean Statut d'activation de la découverte. Example: false
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Découverte mise à jour avec succès",
     *   "data": {
     *     "id": 1,
     *     "title": "Nouvelle collection Breitling mise à jour",
     *     "content": "Découvrez notre collection mise à jour...",
     *     "image": "https://example.com/new-image.jpg",
     *     "is_active": false,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Erreur lors de la mise à jour de la découverte",
     *   "error": "No query results for model [App\\Models\\Discovery] 1"
     * }
     *
     * @param Request $request Données de la requête
     * @param int $id Identifiant de la découverte
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $discovery = Discovery::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'content' => 'string',
                'image' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $discovery->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Découverte mise à jour avec succès',
                'data' => $discovery
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la découverte',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Supprimer une découverte
     *
     * @urlParam id int required L'ID de la découverte à supprimer. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Découverte supprimée avec succès"
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Erreur lors de la suppression de la découverte",
     *   "error": "No query results for model [App\\Models\\Discovery] 1"
     * }
     *
     * @param int $id Identifiant de la découverte
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $discovery = Discovery::findOrFail($id);
            $discovery->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Découverte supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la découverte',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
