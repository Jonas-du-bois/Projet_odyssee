<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizInstance;
use App\Models\QuizType;
use App\Models\UserAnswer;
use App\Models\UserQuizScore;
use App\Models\Chapter;
use App\Models\Unit;
use App\Models\Discovery;
use App\Models\Event;
use App\Models\Weekly;
use App\Models\Novelty;
use App\Models\Reminder;
use App\Models\Choice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * @group Quiz
 * 
 * API pour la gestion des sessions de quiz et des réponses
 */
class QuizController extends Controller
{
    /**
     * Lister tous les types de quiz disponibles
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "nom": "Standard Quiz",
     *       "base_points": 1000,
     *       "speed_bonus": 5,
     *       "gives_ticket": false,
     *       "bonus_multiplier": 1,
     *       "instances_count": 25
     *     }
     *   ]
     * }
     */
    public function getQuizTypes(): JsonResponse
    {
        try {
            $quizTypes = QuizType::withCount('quizInstances')->get();

            return response()->json([
                'success' => true,
                'data' => $quizTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des types de quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Lister les instances de quiz de l'utilisateur
     *
     * @queryParam status string Filtrer par statut (pending, completed, all). Example: completed
     * @queryParam quiz_type_id integer Filtrer par type de quiz. Example: 1
     * @queryParam limit integer Limite de résultats (par défaut: 50). Example: 20
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "instances": [
     *       {
     *         "id": 1,
     *         "quiz_type_id": 1,
     *         "quizable_type": "unit",
     *         "quizable_id": 1,
     *         "quiz_mode": "standard",
     *         "launch_date": "2025-01-10T10:00:00.000000Z",
     *         "quiz_type": {
     *           "id": 1,
     *           "nom": "Standard Quiz",
     *           "base_points": 1000,
     *           "speed_bonus": 5,
     *           "gives_ticket": false,
     *           "bonus_multiplier": 1
     *         },
     *         "quizable": {
     *           "id": 1,
     *           "title": "Introduction à l'horlogerie",
     *           "description": "Quiz sur les concepts de cette unité",
     *           "type": "unit",
     *           "is_available": true,
     *           "is_replayable": true,
     *           "quiz_mode": "standard"
     *         },
     *         "module": {
     *           "id": 1,
     *           "name": "Introduction à l'horlogerie",
     *           "type": "Unit"
     *         },
     *         "user_quiz_score": {
     *           "total_points": 8500,
     *           "total_time": 120,
     *           "ticket_obtained": false,
     *           "percentage": 85.0
     *         }
     *       }
     *     ],
     *     "stats": {
     *       "total_instances": 15,
     *       "completed_instances": 12,
     *       "pending_instances": 3,
     *       "average_score": 85.5,
     *       "total_points": 125000
     *     }
     *   }
     * }
     */
    public function getUserQuizInstances(Request $request): JsonResponse
    {        try {            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:pending,completed,all,started,in_progress',
                'quiz_type_id' => 'nullable|exists:quiz_types,id',
                'module_type' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $limit = $request->get('limit', 50);
              // Base query avec chargement des relations polymorphiques
            $query = QuizInstance::where('user_id', $userId)
                ->with(['quizType', 'userQuizScore', 'quizable']);            // Apply filters
            if ($request->status === 'completed') {
                $query->completed();
            } elseif ($request->status === 'pending') {
                $query->pending();
            } elseif ($request->status === 'started' || $request->status === 'in_progress') {
                $query->where('status', 'started');
            }            if ($request->quiz_type_id) {
                $query->where('quiz_type_id', $request->quiz_type_id);
            }

            if ($request->module_type) {
                $query->where('module_type', $request->module_type);
            }

            // Get instances with pagination
            $instances = $query->orderBy('launch_date', 'desc')
                ->limit($limit)
                ->get();

            // Enrich with module data
            $enrichedInstances = $instances->map(function ($instance) {
                return $this->enrichQuizInstanceWithModule($instance);
            });

            // Calculate stats
            $allUserInstances = QuizInstance::where('user_id', $userId)->with('userQuizScore')->get();
            $stats = $this->calculateUserQuizStats($allUserInstances);

            return response()->json([
                'success' => true,
                'data' => [
                    'instances' => $enrichedInstances,
                    'stats' => $stats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des instances de quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques détaillées des quiz de l'utilisateur
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "total_instances": 15,
     *     "completed_instances": 12,
     *     "pending_instances": 3,
     *     "average_score": 85.5,
     *     "total_points": 125000,
     *     "best_score": 98.5,
     *     "completion_rate": 80,
     *     "quiz_types_stats": [
     *       {
     *         "quiz_type_id": 1,
     *         "quiz_type_name": "Standard Quiz",
     *         "instances_count": 8,
     *         "average_score": 87.5,
     *         "best_score": 95
     *       }
     *     ]
     *   }
     * }
     */
    public function getUserStats(): JsonResponse
    {
        try {
            $userId = Auth::id();
            $allUserInstances = QuizInstance::where('user_id', $userId)->with(['userQuizScore', 'quizType'])->get();
            $stats = $this->calculateUserQuizStats($allUserInstances);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer une instance de quiz spécifique
     *
     * @param int $id - ID de l'instance de quiz
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 24,
     *     "quiz_type_id": 1,
     *     "user_id": 1,
     *     "status": "started",
     *     "launch_date": "2025-06-09T14:30:00",
     *     "questions": [...],
     *     "quiz_type": {...},
     *     "module": {...}
     *   }
     * }
     */
    public function getInstance($id): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Récupérer l'instance avec toutes les relations nécessaires
            $instance = QuizInstance::where('id', $id)
                ->where('user_id', $userId)
                ->with(['quizType', 'userQuizScore', 'quizable'])
                ->first();

            if (!$instance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instance de quiz non trouvée ou accès non autorisé'
                ], 404);
            }            // Préparer les données de base de l'instance
            $responseData = [
                'id' => $instance->id,
                'quiz_type_id' => $instance->quiz_type_id,
                'user_id' => $instance->user_id,
                'status' => $instance->status,
                'launch_date' => $instance->launch_date,
                'quiz_type' => [
                    'id' => $instance->quizType->id,
                    'name' => $instance->quizType->name,
                    'base_points' => $instance->quizType->base_points,
                    'speed_bonus' => $instance->quizType->speed_bonus,
                    'gives_ticket' => $instance->quizType->gives_ticket,
                    'bonus_multiplier' => $instance->quizType->bonus_multiplier
                ]
            ];

            // Ajouter les informations du module si présent
            if ($instance->quizable) {
                $responseData['quizable'] = [
                    'id' => $instance->quizable->id,
                    'title' => $instance->quizable->getQuizTitle(),
                    'description' => $instance->quizable->getQuizDescription(),
                    'type' => $instance->quizable_type
                ];
            }            // Ajouter les questions si le quiz est commencé
            if ($instance->status === 'started' || $instance->status === 'active') {
                $questions = collect();
                
                if ($instance->quizable) {
                    // Questions spécifiques au module
                    $questionOptions = [
                        'quiz_type_id' => $instance->quiz_type_id,
                        'quiz_mode' => $instance->quiz_mode,
                        'user' => Auth::user()
                    ];
                    $questions = $instance->quizable->getQuestions($questionOptions);
                } else {
                    // Questions liées aux unités via le système polymorphique
                    if ($instance->quizable_type === 'App\\\\Models\\\\Unit' && $instance->quizable_id) {
                        // Questions spécifiques à l\'unité
                        $questions = Question::where('quizable_type', 'App\\\\Models\\\\Unit')
                            ->where('quizable_id', $instance->quizable_id)
                            ->with('choices') // Charger tous les attributs des choix
                            ->inRandomOrder()
                            ->limit(10)
                            ->get();
                    } else {
                        // Questions générales (toutes les questions disponibles)
                        $questions = Question::with('choices') // Charger tous les attributs des choix
                            ->inRandomOrder()
                            ->limit(10)
                            ->get();
                    }
                }
                // Formatter les questions pour la réponse
                $formattedQuestions = $questions->map(function($question) {
                    $choices = $question->choices->values(); // S\'assurer que les indices sont consécutifs
                    $correctAnswerIndex = 0;
                    
                    // Trouver l\'index de la bonne réponse
                    foreach ($choices as $index => $choice) {
                        if ($choice->is_correct) {
                            $correctAnswerIndex = $index;
                            break;
                        }
                    }
                    
                    return [
                        'id' => $question->id,
                        'question_text' => $question->question_text,
                        'statement' => $question->question_text, // Alias pour compatibilité
                        'choices' => $choices->map(function($choice) {
                            return [
                                'id' => $choice->id,
                                'choice_text' => $choice->text,
                                'text' => $choice->text // Alias pour compatibilité
                            ];
                        }),
                        'correct_answer' => $correctAnswerIndex,
                        'correct_answer_index' => $correctAnswerIndex
                    ];
                });

                $responseData['questions'] = $formattedQuestions;
                $responseData['total_questions'] = $formattedQuestions->count();
                $responseData['time_limit'] = 300; // 5 minutes par défaut
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'instance de quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Démarrer une nouvelle session de quiz
     *
     * @bodyParam quiz_type_id integer required L'ID du type de quiz. Example: 1
     * @bodyParam quizable_type string optional Le type de module quiz (unit, discovery, event, weekly, novelty, reminder). Example: unit
     * @bodyParam quizable_id integer optional L'ID du module associé. Example: 5
     * @bodyParam quiz_mode string optional Mode de quiz personnalisé. Example: practice
     * @bodyParam chapter_id integer optional L'ID du chapitre (pour backward compatibility). Example: 3
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Quiz démarré avec succès",
     *   "data": {
     *     "quiz_instance_id": 123,
     *     "quiz_type": {
     *       "id": 1,
     *       "nom": "Standard Quiz",
     *       "base_points": 1000,
     *       "speed_bonus": 5
     *     },
     *     "quizable": {
     *       "id": 5,
     *       "title": "Introduction à l'horlogerie",
     *       "type": "unit"
     *     },
     *     "questions": [
     *       {
     *         "id": 45,
     *         "question_text": "Quelle est la fréquence d'un mouvement mécanique standard?",
     *         "choices": [
     *           {
     *             "id": 180,
     *             "choice_text": "28 800 vibrations/heure"
     *           },
     *           {
     *             "id": 181,
     *             "choice_text": "21 600 vibrations/heure"
     *           }
     *         ]
     *       }
     *     ],
     *     "total_questions": 10,
     *     "time_limit": 300
     *   }
     * }
     */
    public function start(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quiz_type_id' => 'required|exists:quiz_types,id',
                'unit_id' => 'nullable|exists:units,id', // Support pour unit_id
                'quizable_type' => 'nullable|string|in:unit,discovery,event,weekly,novelty,reminder',
                'quizable_id' => 'nullable|integer',
                'quiz_mode' => 'nullable|string',
                'chapter_id' => 'nullable|exists:chapters,id', // Backward compatibility
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $quizType = QuizType::findOrFail($request->quiz_type_id);
            
            // Résoudre le module quizable si spécifié
            $quizableEntity = null;
            $quizableTypeString = null; // Pour stocker la chaîne de type comme 'App\\Models\\Unit'

            if ($request->filled('unit_id')) { // Priorité à unit_id pour la compatibilité
                $quizableEntity = Unit::find($request->unit_id);
                if ($quizableEntity) {
                    $quizableTypeString = 'App\\\\Models\\\\Unit';
                }
            } elseif ($request->filled('quizable_type') && $request->filled('quizable_id')) {
                $quizableClass = $this->mapQuizableTypeToClass($request->quizable_type);
                if ($quizableClass) {
                    $quizableEntity = $quizableClass::find($request->quizable_id);
                    if ($quizableEntity) {
                        $quizableTypeString = $quizableClass;
                    }
                } else {
                    return response()->json(['success' => false, 'message' => 'Type de module quizable non valide.'], 400);
                }
            }

            if (($request->filled('unit_id') || ($request->filled('quizable_type') && $request->filled('quizable_id'))) && !$quizableEntity) {
                return response()->json(['success' => false, 'message' => 'Module quiz non trouvé.'], 404);
            }
            
            // Logique de compatibilité ascendante pour chapter_id si quizable_type n\'est pas fourni
            // et qu\'aucun quizableEntity n\'a été trouvé via unit_id ou quizable_type/quizable_id
            $questions = collect();

            if (!$quizableEntity && $request->filled('chapter_id')) {
                $chapter = Chapter::find($request->chapter_id);
                if ($chapter) {
                    // Récupérer des questions de toutes les unités du chapitre.
                    $questions = Question::whereHasMorph('quizable', [Unit::class], function ($query) use ($request) {
                        $query->where('chapter_id', $request->chapter_id);
                    })
                    ->with('choices') // Charger tous les attributs des choix
                    ->inRandomOrder()
                    ->limit(10) 
                    ->get();
                } else {
                    return response()->json(['success' => false, 'message' => 'Chapitre non trouvé.'], 404);
                }
            } elseif ($quizableEntity) {
                 // Récupérer les questions pour le quizableEntity identifié
                if (method_exists($quizableEntity, 'getQuestions')) {
                    $options = [
                        'quiz_type_id' => $quizType->id,
                        'quiz_mode' => $request->quiz_mode ?? 'standard',
                        'user' => $user
                    ];
                    $questions = $quizableEntity->getQuestions($options);
                } else { // Cas spécifique pour Unit ou autre modèle si getQuestions n\'est pas utilisé/défini
                    $questions = Question::where('quizable_type', $quizableTypeString) // Utilise la chaîne de type correcte
                        ->where('quizable_id', $quizableEntity->id)
                        ->with('choices')
                        ->inRandomOrder()
                        ->limit(10) 
                        ->get();
                }
            } else {
                // Fallback: questions générales si aucun module spécifique n\'est trouvé ou applicable
                $questions = Question::with('choices')
                    ->inRandomOrder()
                    ->limit(10)
                    ->get();
            }

            if ($questions->isEmpty()) {
                 return response()->json(['success' => false, 'message' => 'Aucune question trouvée pour ce module ou chapitre.'], 404);
            }
            
            // Créer une instance de quiz
            $quizInstance = QuizInstance::create([
                'user_id' => $user->id,
                'quiz_type_id' => $quizType->id,
                'quizable_type' => $quizableEntity ? $quizableTypeString : null, // Stocke le type de classe complet
                'quizable_id' => $quizableEntity ? $quizableEntity->id : null,
                'quiz_mode' => $request->quiz_mode ?? ($quizableEntity && method_exists($quizableEntity, 'getDefaultQuizMode') ? $quizableEntity->getDefaultQuizMode() : 'standard'),
                'launch_date' => now(),
                'status' => 'started',
                // Si chapter_id a été utilisé pour trouver des questions sans quizable direct, on pourrait le stocker ici aussi.
                // 'chapter_id' => !$quizableEntity && $request->filled('chapter_id') ? $request->chapter_id : null, 
            ]);

            // Formatter les questions pour la réponse
            $formattedQuestions = $questions->map(function($question) {
                $choices = $question->choices->values();
                $correctAnswerIndex = $choices->search(function($choice) {
                    return $choice->is_correct;
                });
                 // Si aucune réponse correcte n\'est explicitement marquée (ce qui ne devrait pas arriver avec un bon seeder),
                // on pourrait définir un comportement par défaut ou logger une erreur. Pour l\'instant, on laisse à false ou 0.
                $correctAnswerIndex = $correctAnswerIndex === false ? 0 : $correctAnswerIndex;

                return [
                    'id' => $question->id,
                    'question_text' => $question->question_text,
                    'statement' => $question->question_text,
                    'choices' => $choices->map(function($choice) {
                        return [
                            'id' => $choice->id,
                            'choice_text' => $choice->text,
                            'text' => $choice->text
                        ];
                    }),
                    'correct_answer' => $correctAnswerIndex, // L\'index de la bonne réponse
                    'correct_answer_index' => $correctAnswerIndex // Pour compatibilité
                ];
            });

            $responseData = [
                'quiz_instance_id' => $quizInstance->id,
                'quiz_type' => [
                    'id' => $quizType->id,
                    'name' => $quizType->name,
                    'base_points' => $quizType->base_points,
                    'speed_bonus' => $quizType->speed_bonus,
                    'gives_ticket' => $quizType->gives_ticket,
                    'bonus_multiplier' => $quizType->bonus_multiplier
                ],
                'questions' => $formattedQuestions,
                'total_questions' => $formattedQuestions->count(),
                'time_limit' => 300 // 5 minutes par défaut
            ];

            if ($quizableEntity) {
                $responseData['quizable'] = [
                    'id' => $quizableEntity->id,
                    'title' => $quizableEntity->getQuizTitle(), // Assurez-vous que cette méthode existe sur vos modèles quizables
                    'description' => $quizableEntity->getQuizDescription(), // Assurez-vous que cette méthode existe
                    'type' => $request->quizable_type ?? ($request->unit_id ? 'unit' : null) // type court comme \'unit\', \'discovery\'
                ];
            } elseif ($request->filled('chapter_id') && isset($chapter)) {
                 $responseData['chapter'] = [ // Info supplémentaire si le quiz est basé sur un chapitre
                    'id' => $chapter->id,
                    'title' => $chapter->title
                ];
            }


            return response()->json([
                'success' => true,
                'message' => 'Quiz démarré avec succès',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error("Erreur lors du démarrage du quiz: " . $e->getMessage() . " Stack: " . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du démarrage du quiz.', // Message générique pour l'utilisateur
                'error' => $e->getMessage() // Peut être omis en production pour la sécurité
            ], 500);
        }
    }

    // Helper pour mapper le type court (ex: \'unit\') à la classe complète (ex: \'App\\Models\\Unit\')
    private function mapQuizableTypeToClass(string $type): ?string
    {
        $map = [
            'unit' => Unit::class,
            'discovery' => Discovery::class,
            'event' => Event::class,
            'weekly' => Weekly::class,
            'novelty' => Novelty::class,
            'reminder' => Reminder::class,
            // Ajoutez d\'autres types si nécessaire
        ];
        return $map[strtolower($type)] ?? null;
    }

    /**
     * Soumettre les réponses d\'un quiz
     *
     * @bodyParam quiz_instance_id integer required L'ID de l'instance de quiz. Example: 123
     * @bodyParam answers array required Les réponses du quiz.
     * @bodyParam answers.*.question_id integer required L'ID de la question. Example: 45
     * @bodyParam answers.*.choice_id integer required L'ID du choix sélectionné. Example: 180
     * @bodyParam answers.*.time_taken integer optional Temps pris pour répondre en secondes. Example: 15
     * @bodyParam total_time integer optional Temps total du quiz en secondes. Example: 245
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Réponses soumises avec succès",
     *   "data": {
     *     "score": 8,
     *     "total_questions": 10,
     *     "percentage": 80,
     *     "total_points": 8500,
     *     "speed_bonus": 500,
     *     "time_bonus": 200,
     *     "ticket_obtained": false,
     *     "quiz_instance_id": 123,
     *     "detailed_results": [
     *       {
     *         "question_id": 45,
     *         "is_correct": true,
     *         "points_earned": 1000
     *       }
     *     ]
     *   }
     * }
     */    public function submitAnswers(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quiz_instance_id' => 'required|exists:quiz_instances,id',
                'answers' => 'required|array|min:1',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.choice_id' => 'required|exists:choices,id',
                'answers.*.time_taken' => 'nullable|integer|min:1',
                'total_time' => 'nullable|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que le quiz appartient à l'utilisateur
            $quizInstance = QuizInstance::with('quizType')->findOrFail($request->quiz_instance_id);
            
            if ($quizInstance->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce quiz ne vous appartient pas'
                ], 403);
            }

            if ($quizInstance->status == 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce quiz est déjà terminé'
                ], 400);
            }

            $score = 0;
            $totalQuestions = count($request->answers);
            $totalTime = $request->total_time ?? 300;
            $detailedResults = [];

            DB::beginTransaction();
            
            try {
                // Enregistrer les réponses et calculer le score
                foreach ($request->answers as $answer) {
                    $question = Question::with('choices')->findOrFail($answer['question_id']);
                    $choice = $question->choices->find($answer['choice_id']);
                    
                    $isCorrect = $choice && $choice->is_correct;
                    
                    if ($isCorrect) {
                        $score++;
                    }                    UserAnswer::create([
                        'user_id' => Auth::id(),
                        'quiz_instance_id' => $request->quiz_instance_id,
                        'question_id' => $answer['question_id'],
                        'choice_id' => $answer['choice_id'],
                        'is_correct' => $isCorrect,
                        'response_time' => $answer['time_taken'] ?? 10, // Temps par défaut de 10 secondes
                        'points_obtained' => $isCorrect ? $quizInstance->quizType->base_points : 0,
                        'date' => now(),
                    ]);

                    $detailedResults[] = [
                        'question_id' => $answer['question_id'],
                        'is_correct' => $isCorrect,
                        'points_earned' => $isCorrect ? $quizInstance->quizType->base_points : 0
                    ];
                }

                // Calculer les points avec bonus
                $basePoints = $score * $quizInstance->quizType->base_points;
                $speedBonus = $this->calculateSpeedBonus($totalTime, $quizInstance->quizType);
                $timeBonus = $this->calculateTimeBonus($totalTime);
                $totalPoints = $basePoints + $speedBonus + $timeBonus;

                // Vérifier si l'utilisateur obtient un ticket
                $ticketObtained = $quizInstance->quizType->gives_ticket && 
                                  (($score / $totalQuestions) >= 0.8); // 80% minimum

                // Mettre à jour le statut du quiz
                $quizInstance->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'total_time' => $totalTime
                ]);                // Créer l'entrée de score
                $userQuizScore = UserQuizScore::create([
                    'quiz_instance_id' => $request->quiz_instance_id,
                    'total_points' => $totalPoints,
                    'total_time' => $totalTime,
                    'ticket_obtained' => $ticketObtained,
                    'bonus_obtained' => ($speedBonus > 0 || $timeBonus > 0), // true si des bonus ont été obtenus
                ]);                DB::commit();

                $percentage = ($totalQuestions > 0) ? ($score / $totalQuestions) * 100 : 0;

                return response()->json([
                    'success' => true,
                    'message' => 'Réponses soumises avec succès',
                    'data' => [
                        'score' => $score,
                        'total_questions' => $totalQuestions,
                        'percentage' => $percentage,
                        'total_points' => $totalPoints,
                        'speed_bonus' => $speedBonus,
                        'time_bonus' => $timeBonus,
                        'ticket_obtained' => $ticketObtained,
                        'quiz_instance_id' => $request->quiz_instance_id,
                        'detailed_results' => $detailedResults
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission des réponses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le résultat détaillé d'un quiz
     *
     * @urlParam id integer required L'ID de l'instance de quiz. Example: 123
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "quiz_instance": {
     *       "id": 123,
     *       "status": "completed",
     *       "launch_date": "2025-01-10T10:00:00.000000Z",
     *       "completed_at": "2025-01-10T10:05:00.000000Z",
     *       "total_time": 245,
     *       "quiz_type": {
     *         "id": 1,
     *         "nom": "Standard Quiz"
     *       }
     *     },
     *     "score": {
     *       "score": 8,
     *       "total_questions": 10,
     *       "percentage": 80,
     *       "total_points": 8500,
     *       "speed_bonus": 500,
     *       "time_bonus": 200,
     *       "ticket_obtained": false
     *     },
     *     "answers": [
     *       {
     *         "question_id": 45,
     *         "choice_id": 180,
     *         "is_correct": true,
     *         "time_taken": 15,
     *         "question": {
     *           "question_text": "Quelle est la fréquence d'un mouvement mécanique standard?"
     *         },
     *         "choice": {
     *           "choice_text": "28 800 vibrations/heure"
     *         }
     *       }
     *     ]
     *   }
     * }
     */
    public function getResult($id): JsonResponse
    {
        try {
            $quizInstance = QuizInstance::with([
                'userAnswers.question',
                'userAnswers.choice',
                'userQuizScore',
                'quizType'
            ])->findOrFail($id);
            
            if ($quizInstance->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce quiz ne vous appartient pas'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_instance' => [
                        'id' => $quizInstance->id,
                        'status' => $quizInstance->status,
                        'launch_date' => $quizInstance->launch_date,
                        'completed_at' => $quizInstance->completed_at,
                        'total_time' => $quizInstance->total_time,                        'quiz_type' => [
                            'id' => $quizInstance->quizType->id,
                            'name' => $quizInstance->quizType->name,
                            'base_points' => $quizInstance->quizType->base_points,
                            'speed_bonus' => $quizInstance->quizType->speed_bonus
                        ]
                    ],
                    'score' => $quizInstance->userQuizScore,                    'answers' => $quizInstance->userAnswers->map(function($answer) {
                        return [
                            'question_id' => $answer->question_id,
                            'choice_id' => $answer->choice_id,
                            'is_correct' => $answer->is_correct,
                            'time_taken' => $answer->time_taken,                            'question' => [
                                'question_text' => $answer->question->question_text // Utiliser le champ question_text
                            ],                            'choice' => [
                                'choice_text' => $answer->choice->text // Utiliser le champ text
                            ]
                        ];
                    })
                ]
            ]);        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des résultats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enrichir une instance de quiz avec les données du module associé
     * Utilise la nouvelle architecture polymorphique
     */
    private function enrichQuizInstanceWithModule($instance)
    {
        $enriched = $instance->toArray();
        
        // Utiliser la relation polymorphique pour obtenir les données du module
        if ($instance->quizable) {
            $quizable = $instance->quizable;
            $enriched['quizable'] = [
                'id' => $quizable->id,
                'title' => $quizable->getQuizTitle(),
                'description' => $quizable->getQuizDescription(),
                'type' => $instance->quizable_type,
                'is_available' => $quizable->isAvailable($instance->user),
                'is_replayable' => $quizable->isReplayable(),
                'quiz_mode' => $instance->quiz_mode
            ];
            
            // Backward compatibility : ajouter les anciens champs 'module'
            $enriched['module'] = [
                'id' => $quizable->id,
                'name' => $quizable->getQuizTitle(),
                'type' => ucfirst($instance->quizable_type) // unit -> Unit pour BC
            ];
        }

        return $enriched;
    }

    /**
     * Résoudre le module quizable selon son type
     * Clean Code : utilise la morph map pour résoudre les classes
     */    private function resolveQuizable(string $quizableType, int $quizableId)
    {
        // Utiliser la morph map pour résoudre le bon modèle
        $morphMap = \Illuminate\Database\Eloquent\Relations\Relation::$morphMap ?? [];
        
        if (!isset($morphMap[$quizableType])) {
            return null;
        }

        $modelClass = $morphMap[$quizableType];
        
        if (!class_exists($modelClass)) {
            return null;
        }

        // Charger les relations nécessaires selon le type de module
        $with = [];
        switch ($quizableType) {
            case 'discovery':
                $with = ['chapter.units.questions.choices'];
                break;
            case 'unit':
                $with = ['questions.choices'];
                break;
            case 'event':
            case 'weekly':
            case 'novelty':
            case 'reminder':
                $with = ['questions.choices'];
                break;
        }

        return $modelClass::with($with)->find($quizableId);
    }/**
     * Calculer les statistiques des quiz d'un utilisateur
     */
    private function calculateUserQuizStats($instances)
    {
        $total = $instances->count();
        $completed = $instances->where('status', 'completed')->count();
        $pending = $instances->where('status', 'started')->count();
        
        $completedInstances = $instances->where('status', 'completed');
        $scores = $completedInstances->pluck('userQuizScore.percentage')->filter();
        
        $averageScore = $scores->count() > 0 ? $scores->average() : 0;
        $bestScore = $scores->count() > 0 ? $scores->max() : 0;
        $totalPoints = $completedInstances->sum('userQuizScore.total_points');
        $completionRate = $total > 0 ? ($completed / $total) * 100 : 0;

        // Statistiques par type de quiz
        $quizTypesStats = $instances->groupBy('quiz_type_id')->map(function($typeInstances) {
            $completed = $typeInstances->where('status', 'completed');
            $scores = $completed->pluck('userQuizScore.percentage')->filter();
            return [
                'quiz_type_id' => $typeInstances->first()->quiz_type_id,
                'quiz_type_name' => $typeInstances->first()->quizType->name ?? 'Type inconnu',                'instances_count' => $typeInstances->count(),
                'completed_count' => $completed->count(),
                'average_score' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
                'best_score' => $scores->count() > 0 ? $scores->max() : 0
            ];
        })->values();

        return [
            'total_instances' => $total,
            'completed_instances' => $completed,
            'pending_instances' => $pending,
            'average_score' => round($averageScore, 2),
            'best_score' => $bestScore,
            'total_points' => $totalPoints,
            'completion_rate' => round($completionRate, 2),
            'quiz_types_stats' => $quizTypesStats
        ];
    }

    /**
     * Calculer le bonus de vitesse basé sur le temps
     */
    private function calculateSpeedBonus($totalTime, $quizType)
    {
        $timeLimit = 300; // 5 minutes
        $timeSaved = max(0, $timeLimit - $totalTime);
        
        return $timeSaved * $quizType->speed_bonus;
    }

    /**
     * Calculer le bonus de temps
     */
    private function calculateTimeBonus($totalTime)
    {
        $timeLimit = 300; // 5 minutes
        
        if ($totalTime <= 120) { // Moins de 2 minutes
            return 500;
        } elseif ($totalTime <= 180) { // Moins de 3 minutes
            return 300;
        } elseif ($totalTime <= 240) { // Moins de 4 minutes
            return 100;
        }
        
        return 0;
    }
}
