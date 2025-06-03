<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * @group Reminders
 *
 * API pour gérer les rappels et notifications programmées
 */
class ReminderController extends Controller
{
    /**
     * Lister tous les rappels
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Rappel quiz quotidien",
     *       "message": "N'oubliez pas de faire votre quiz quotidien !",
     *       "start_date": "2024-01-01",
     *       "end_date": "2024-12-31",
     *       "is_active": true,
     *       "priority": 3,
     *       "trigger_rule": "daily",
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
            $reminders = Reminder::all();
            
            return response()->json([
                'success' => true,
                'data' => $reminders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rappels',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Créer un nouveau rappel
     *
     * @bodyParam title string required Le titre du rappel. Example: Rappel quiz quotidien
     * @bodyParam message string required Le message du rappel. Example: N'oubliez pas de faire votre quiz quotidien !
     * @bodyParam start_date date required Date de début du rappel. Example: 2024-01-01
     * @bodyParam end_date date required Date de fin du rappel. Example: 2024-12-31
     * @bodyParam is_active boolean Statut d'activation. Example: true
     * @bodyParam priority int Priorité du rappel (1-5). Example: 3
     * @bodyParam trigger_rule string Règle de déclenchement. Example: daily
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Rappel créé avec succès",
     *   "data": {
     *     "id": 1,
     *     "title": "Rappel quiz quotidien",
     *     "message": "N'oubliez pas de faire votre quiz quotidien !",
     *     "start_date": "2024-01-01",
     *     "end_date": "2024-12-31",
     *     "is_active": true,
     *     "priority": 3,
     *     "trigger_rule": "daily",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "errors": {
     *     "title": ["Le champ title est obligatoire."],
     *     "end_date": ["Le champ end_date doit être une date postérieure à start_date."]
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
                'message' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'is_active' => 'boolean',
                'priority' => 'integer|min:1|max:5',
                'trigger_rule' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $reminder = Reminder::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Rappel créé avec succès',
                'data' => $reminder
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du rappel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un rappel existant
     *
     * @param Request $request Données de la requête
     * @param int $id Identifiant du rappel
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $reminder = Reminder::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'title' => 'string|max:255',
                'message' => 'string',
                'start_date' => 'date',
                'end_date' => 'date|after:start_date',
                'is_active' => 'boolean',
                'priority' => 'integer|min:1|max:5',
                'trigger_rule' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $reminder->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Rappel mis à jour avec succès',
                'data' => $reminder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rappel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un rappel
     *
     * @param int $id Identifiant du rappel
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $reminder = Reminder::findOrFail($id);
            $reminder->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Rappel supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rappel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
