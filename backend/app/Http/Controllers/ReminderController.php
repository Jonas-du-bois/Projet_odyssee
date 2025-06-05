<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * @group Reminders
 *
 * API pour gérer les reminders de quiz de révision Breitling League
 * Les reminders sont des quiz de révision générés automatiquement à partir de questions précédemment vues.
 * Ils sont liés à un chapitre et doivent être faits avant une date limite.
 */
class ReminderController extends Controller
{
    /**
     * Lister tous les reminders actifs
     *
     * Récupère les reminders non expirés avec informations sur les chapitres associés et statut
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "chapter_id": 1,
     *       "nb_questions": 10,
     *       "date_limite": "2025-06-10",
     *       "is_active": true,
     *       "is_expired": false,
     *       "remaining_days": 6,
     *       "is_ending_soon": false,
     *       "chapter": {
     *         "id": 1,
     *         "nom": "Introduction à l'horlogerie",
     *         "description": "Découverte des bases de l'horlogerie"
     *       },
     *       "questions_available": 15
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $reminders = Reminder::active()
                ->with('chapter:id,nom,description')
                ->get()
                ->map(function ($reminder) {
                    // Compter les questions disponibles pour le chapitre
                    $questionsCount = $reminder->getChapterQuestions()->count();
                      return [
                        'id' => $reminder->id,
                        'chapter_id' => $reminder->chapter_id,
                        'number_questions' => $reminder->number_questions,
                        'deadline_date' => Carbon::parse($reminder->deadline_date)->format('Y-m-d'),
                        'is_active' => $reminder->isActive(),
                        'is_expired' => $reminder->isExpired(),
                        'remaining_days' => $reminder->getRemainingDays(),
                        'is_ending_soon' => $reminder->getRemainingDays() <= 3 && $reminder->getRemainingDays() > 0,
                        'chapter' => $reminder->chapter ? [
                            'id' => $reminder->chapter->id,
                            'nom' => $reminder->chapter->nom,
                            'description' => $reminder->chapter->description
                        ] : null,
                        'questions_available' => $questionsCount
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $reminders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des reminders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un reminder spécifique
     *
     * @param int $id ID du reminder
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "nb_questions": 10,
     *     "date_limite": "2025-06-10",
     *     "is_active": true,
     *     "is_expired": false,
     *     "remaining_days": 6,
     *     "chapter": {
     *       "id": 1,
     *       "nom": "Introduction à l'horlogerie",
     *       "description": "Découverte des bases de l'horlogerie"
     *     },
     *     "questions": [
     *       {
     *         "id": 1,
     *         "unit_id": 1,
     *         "texte": "Quelle est la fonction principale d'un ressort de barillet ?",
     *         "type": "multiple_choice"
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Reminder non trouvé"
     * }
     *
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $reminder = Reminder::with('chapter:id,nom,description')->find($id);
            
            if (!$reminder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reminder non trouvé'
                ], 404);
            }

            // Récupérer les questions pour le quiz de révision
            $questions = $reminder->getChapterQuestions();
              $data = [
                'id' => $reminder->id,
                'chapter_id' => $reminder->chapter_id,
                'number_questions' => $reminder->number_questions,
                'deadline_date' => Carbon::parse($reminder->deadline_date)->format('Y-m-d'),
                'is_active' => $reminder->isActive(),
                'is_expired' => $reminder->isExpired(),
                'remaining_days' => $reminder->getRemainingDays(),
                'chapter' => $reminder->chapter ? [
                    'id' => $reminder->chapter->id,
                    'nom' => $reminder->chapter->nom,
                    'description' => $reminder->chapter->description
                ] : null,
                'questions' => $questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'unit_id' => $question->unit_id,
                        'texte' => $question->texte,
                        'type' => $question->type
                    ];
                })
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du reminder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer un nouveau reminder
     *
     * @bodyParam chapter_id int required ID du chapitre. Example: 1
     * @bodyParam nb_questions int required Nombre de questions pour le quiz de révision. Example: 10
     * @bodyParam date_limite string required Date limite au format Y-m-d. Example: 2025-06-10
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Reminder créé avec succès",
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "nb_questions": 10,
     *     "date_limite": "2025-06-10"
     *   }
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Erreurs de validation",
     *   "errors": {
     *     "chapter_id": ["Le chapitre sélectionné n'existe pas"]
     *   }
     * }
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {            $validator = Validator::make($request->all(), [
                'chapter_id' => 'required|integer|exists:chapters,id',
                'number_questions' => 'required|integer|min:1|max:50',
                'deadline_date' => 'required|date|date_format:Y-m-d|after:today',
            ], [
                'chapter_id.required' => 'L\'ID du chapitre est requis',
                'chapter_id.exists' => 'Le chapitre sélectionné n\'existe pas',
                'number_questions.required' => 'Le nombre de questions est requis',
                'number_questions.min' => 'Il faut au moins 1 question',
                'number_questions.max' => 'Maximum 50 questions autorisées',
                'deadline_date.required' => 'La date limite est requise',
                'deadline_date.date_format' => 'La date limite doit être au format Y-m-d',
                'deadline_date.after' => 'La date limite doit être dans le futur',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }            // Vérifier que le chapitre a suffisamment de questions
            $chapter = Chapter::with('units.questions')->find($request->chapter_id);
            $availableQuestions = $chapter->units->pluck('questions')->flatten()->count();
            
            if ($availableQuestions < $request->number_questions) {
                return response()->json([
                    'success' => false,
                    'message' => "Le chapitre ne contient que {$availableQuestions} question(s), impossible de créer un reminder avec {$request->number_questions} questions"
                ], 422);
            }

            $reminder = Reminder::create([
                'chapter_id' => $request->chapter_id,
                'number_questions' => $request->number_questions,
                'deadline_date' => $request->deadline_date,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reminder créé avec succès',
                'data' => [
                    'id' => $reminder->id,
                    'chapter_id' => $reminder->chapter_id,
                    'number_questions' => $reminder->number_questions,
                    'deadline_date' => $reminder->deadline_date,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du reminder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour un reminder
     *
     * @param int $id ID du reminder
     * @bodyParam chapter_id int ID du chapitre. Example: 1
     * @bodyParam nb_questions int Nombre de questions pour le quiz de révision. Example: 15
     * @bodyParam date_limite string Date limite au format Y-m-d. Example: 2025-06-15
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Reminder mis à jour avec succès",
     *   "data": {
     *     "id": 1,
     *     "chapter_id": 1,
     *     "nb_questions": 15,
     *     "date_limite": "2025-06-15"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Reminder non trouvé"
     * }
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $reminder = Reminder::find($id);
            
            if (!$reminder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reminder non trouvé'
                ], 404);
            }            $validator = Validator::make($request->all(), [
                'chapter_id' => 'sometimes|integer|exists:chapters,id',
                'number_questions' => 'sometimes|integer|min:1|max:50',
                'deadline_date' => 'sometimes|date|date_format:Y-m-d|after:today',
            ], [
                'chapter_id.exists' => 'Le chapitre sélectionné n\'existe pas',
                'number_questions.min' => 'Il faut au moins 1 question',
                'number_questions.max' => 'Maximum 50 questions autorisées',
                'deadline_date.date_format' => 'La date limite doit être au format Y-m-d',
                'deadline_date.after' => 'La date limite doit être dans le futur',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }            // Si le chapitre ou le nombre de questions change, vérifier la disponibilité
            $chapterId = $request->has('chapter_id') ? $request->chapter_id : $reminder->chapter_id;
            $numberQuestions = $request->has('number_questions') ? $request->number_questions : $reminder->number_questions;
            
            if ($request->has('chapter_id') || $request->has('number_questions')) {
                $chapter = Chapter::with('units.questions')->find($chapterId);
                $availableQuestions = $chapter->units->pluck('questions')->flatten()->count();
                
                if ($availableQuestions < $numberQuestions) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le chapitre ne contient que {$availableQuestions} question(s), impossible d'avoir {$numberQuestions} questions"
                    ], 422);
                }
            }

            $reminder->update($request->only(['chapter_id', 'number_questions', 'deadline_date']));

            return response()->json([
                'success' => true,
                'message' => 'Reminder mis à jour avec succès',
                'data' => [
                    'id' => $reminder->id,
                    'chapter_id' => $reminder->chapter_id,
                    'number_questions' => $reminder->number_questions,
                    'deadline_date' => $reminder->deadline_date,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du reminder',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un reminder
     *
     * @param int $id ID du reminder
     * @response 200 {
     *   "success": true,
     *   "message": "Reminder supprimé avec succès"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Reminder non trouvé"
     * }
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $reminder = Reminder::find($id);
            
            if (!$reminder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reminder non trouvé'
                ], 404);
            }

            $reminder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reminder supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du reminder',
                'error' => $e->getMessage()            ], 500);
        }
    }
}
