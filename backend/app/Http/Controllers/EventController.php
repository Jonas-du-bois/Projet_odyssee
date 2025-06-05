<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @group Events
 *
 * API pour gérer les événements spéciaux Breitling League
 * Les événements sont des modules spéciaux liés à plusieurs unités via la table event_units.
 * Chaque unité contient sa propre théorie HTML et questions associées.
 * Un événement peut inclure des unités de plusieurs chapitres différents.
 */
class EventController extends Controller
{
    /**
     * Lister tous les événements
     *
     * Récupère tous les événements avec leur statut et informations sur les unités associées
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "theme": "Horlogerie Suisse Excellence",
     *       "date_debut": "2025-06-01",
     *       "date_fin": "2025-06-30",
     *       "is_active": true,
     *       "is_upcoming": false,
     *       "is_finished": false,
     *       "remaining_days": 26,
     *       "is_ending_soon": false,
     *       "units_count": 5,
     *       "total_questions": 50
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $events = Event::withCount('units')
                ->get()
                ->map(function ($event) {                    return [
                        'id' => $event->id,
                        'theme' => $event->theme,
                        'date_debut' => Carbon::parse($event->start_date)->format('Y-m-d'),
                        'date_fin' => Carbon::parse($event->end_date)->format('Y-m-d'),
                        'is_active' => $event->isActive(),
                        'is_upcoming' => $event->isUpcoming(),
                        'is_finished' => $event->isFinished(),
                        'remaining_days' => $event->getRemainingDays(),
                        'is_ending_soon' => $event->getRemainingDays() <= 3 && $event->getRemainingDays() > 0,
                        'units_count' => $event->units_count,
                        'total_questions' => $event->getTotalQuestionsCount()
                    ];
                });
            
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
    }

    /**
     * Créer un nouvel événement
     *
     * @bodyParam theme string required Le thème de l'événement. Example: Horlogerie Suisse Excellence
     * @bodyParam date_debut string required Date de début au format Y-m-d. Example: 2025-06-01
     * @bodyParam date_fin string required Date de fin au format Y-m-d. Example: 2025-06-30
     * @bodyParam unit_ids array Liste des IDs des unités à associer à l'événement. Example: [1, 2, 3]
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Événement créé avec succès",
     *   "data": {
     *     "id": 1,
     *     "theme": "Horlogerie Suisse Excellence",
     *     "date_debut": "2025-06-01",
     *     "date_fin": "2025-06-30"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Erreurs de validation",
     *   "errors": {
     *     "theme": ["Le thème est requis"]
     *   }
     * }
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'theme' => 'required|string|max:255',
                'date_debut' => 'required|date|date_format:Y-m-d',
                'date_fin' => 'required|date|date_format:Y-m-d|after:date_debut',
                'unit_ids' => 'array',
                'unit_ids.*' => 'integer|exists:units,id',
            ], [
                'theme.required' => 'Le thème est requis',
                'date_debut.required' => 'La date de début est requise',
                'date_debut.date_format' => 'La date de début doit être au format Y-m-d',
                'date_fin.required' => 'La date de fin est requise',
                'date_fin.date_format' => 'La date de fin doit être au format Y-m-d',
                'date_fin.after' => 'La date de fin doit être postérieure à la date de début',
                'unit_ids.array' => 'Les IDs des unités doivent être un tableau',
                'unit_ids.*.exists' => 'Une ou plusieurs unités sélectionnées n\'existent pas',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }            $event = Event::create([
                'theme' => $request->theme,
                'start_date' => $request->date_debut,
                'end_date' => $request->date_fin,
            ]);

            // Associer les unités si fournies
            if ($request->has('unit_ids') && is_array($request->unit_ids)) {
                $event->units()->sync($request->unit_ids);
            }            return response()->json([
                'success' => true,
                'message' => 'Événement créé avec succès',
                'data' => [
                    'id' => $event->id,
                    'theme' => $event->theme,
                    'date_debut' => $event->start_date,
                    'date_fin' => $event->end_date,
                ]
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
     * Mettre à jour un événement
     *
     * @param int $id ID de l'événement
     * @bodyParam theme string Le thème de l'événement. Example: Horlogerie Moderne
     * @bodyParam date_debut string Date de début au format Y-m-d. Example: 2025-07-01
     * @bodyParam date_fin string Date de fin au format Y-m-d. Example: 2025-07-31
     * @bodyParam unit_ids array Liste des IDs des unités à associer. Example: [1, 2, 4]
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Événement mis à jour avec succès",
     *   "data": {
     *     "id": 1,
     *     "theme": "Horlogerie Moderne",
     *     "date_debut": "2025-07-01",
     *     "date_fin": "2025-07-31"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Événement non trouvé"
     * }
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'theme' => 'sometimes|string|max:255',
                'date_debut' => 'sometimes|date|date_format:Y-m-d',
                'date_fin' => 'sometimes|date|date_format:Y-m-d|after:date_debut',
                'unit_ids' => 'array',
                'unit_ids.*' => 'integer|exists:units,id',
            ], [
                'theme.string' => 'Le thème doit être une chaîne de caractères',
                'date_debut.date_format' => 'La date de début doit être au format Y-m-d',
                'date_fin.date_format' => 'La date de fin doit être au format Y-m-d',
                'date_fin.after' => 'La date de fin doit être postérieure à la date de début',
                'unit_ids.array' => 'Les IDs des unités doivent être un tableau',
                'unit_ids.*.exists' => 'Une ou plusieurs unités sélectionnées n\'existent pas',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $event->update([
                'theme' => $request->theme ?? $event->theme,
                'start_date' => $request->date_debut ?? $event->start_date,
                'end_date' => $request->date_fin ?? $event->end_date,
            ]);

            // Mettre à jour les unités associées si fournies
            if ($request->has('unit_ids')) {
                $event->units()->sync($request->unit_ids);
            }            return response()->json([
                'success' => true,
                'message' => 'Événement mis à jour avec succès',
                'data' => [
                    'id' => $event->id,
                    'theme' => $event->theme,
                    'date_debut' => $event->start_date,
                    'date_fin' => $event->end_date,
                ]
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
     * @param int $id ID de l'événement
     * @response 200 {
     *   "success": true,
     *   "message": "Événement supprimé avec succès"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Événement non trouvé"
     * }
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            // Supprimer les associations avec les unités avant de supprimer l'événement
            $event->units()->detach();
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

    /**
     * Récupérer les unités d'un événement
     *
     * Retourne toutes les unités associées à l'événement avec leur théorie HTML et questions
     *
     * @param int $eventId ID de l'événement
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "titre": "Introduction aux mouvements",
     *       "description": "Découverte des différents types de mouvements horlogers",
     *       "theorie_html": "<h2>Les mouvements horlogers</h2><p>Un mouvement horloger...</p>",
     *       "chapter": {
     *         "id": 1,
     *         "titre": "Horlogerie de base",
     *         "description": "Les fondamentaux de l'horlogerie"
     *       },
     *       "questions_count": 5,
     *       "questions": [
     *         {
     *           "id": 1,
     *           "enonce": "Qu'est-ce qu'un mouvement mécanique ?",
     *           "type": "multiple_choice",
     *           "timer_secondes": 30,
     *           "choices": [
     *             {
     *               "id": 1,
     *               "texte": "Un mouvement actionné par un ressort",
     *               "est_correct": true
     *             }
     *           ]
     *         }
     *       ]
     *     }
     *   ]
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Événement non trouvé"
     * }
     *
     * @return JsonResponse
     */
    public function units($eventId): JsonResponse
    {
        try {
            $event = Event::find($eventId);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé'
                ], 404);
            }

            $units = $event->getUnitsWithContent();

            return response()->json([
                'success' => true,
                'data' => $units
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des unités de l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
