<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @group Events
 *
 * API pour gérer les événements spéciaux de la plateforme
 */
class EventController extends Controller
{
    /**
     * Lister tous les événements
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Concours d'été Breitling",
     *       "description": "Participez à notre grand concours d'été avec des prix exceptionnels !",
     *       "start_date": "2024-06-01",
     *       "end_date": "2024-08-31",
     *       "is_active": true,
     *       "image": "https://example.com/event-image.jpg",
     *       "theme_color": "#ff6b35",
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
            $events = Event::all();
            
            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Créer un nouvel événement
     *
     * @bodyParam title string required Le titre de l'événement. Example: Concours d'été Breitling
     * @bodyParam description string required La description de l'événement. Example: Participez à notre grand concours d'été avec des prix exceptionnels !
     * @bodyParam start_date date required Date de début de l'événement. Example: 2024-06-01
     * @bodyParam end_date date required Date de fin de l'événement. Example: 2024-08-31
     * @bodyParam is_active boolean Statut d'activation. Example: true
     * @bodyParam image string URL de l'image de l'événement. Example: https://example.com/event-image.jpg
     * @bodyParam theme_color string Couleur thématique de l'événement. Example: #ff6b35
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Événement créé avec succès",
     *   "data": {
     *     "id": 1,
     *     "title": "Concours d'été Breitling",
     *     "description": "Participez à notre grand concours d'été avec des prix exceptionnels !",
     *     "start_date": "2024-06-01",
     *     "end_date": "2024-08-31",
     *     "is_active": true,
     *     "image": "https://example.com/event-image.jpg",
     *     "theme_color": "#ff6b35",
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
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean',
                'image' => 'nullable|string',
                'theme_color' => 'nullable|string',
                'bonus_points' => 'integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $event = Event::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Événement créé avec succès',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un événement existant
     *
     * @param Request $request Données de la requête
     * @param int $id Identifiant de l'événement
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'description' => 'string',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'is_active' => 'boolean',
                'image' => 'nullable|string',
                'theme_color' => 'nullable|string',
                'bonus_points' => 'integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $event->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Événement mis à jour avec succès',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un événement
     *
     * @param int $id Identifiant de l'événement
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $event = Event::findOrFail($id);
            $event->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Événement supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
