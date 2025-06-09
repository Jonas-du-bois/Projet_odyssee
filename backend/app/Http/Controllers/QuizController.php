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
            }

            // Ajouter les questions si le quiz est commencé
            if ($instance->status === 'started') {
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
                    // Questions générales par type de quiz
                    $questions = Question::where('quiz_type_id', $instance->quiz_type_id)
                        ->with(['choices' => function($query) {
                            $query->select('id', 'question_id', 'text');
                        }])
                        ->inRandomOrder()
                        ->limit(10)
                        ->get();
                }

                // Formatter les questions pour la réponse
                $formattedQuestions = $questions->map(function($question) {
                    return [
                        'id' => $question->id,
                        'question_text' => $question->statement,
                        'choices' => $question->choices->map(function($choice) {
                            return [
                                'id' => $choice->id,
                                'choice_text' => $choice->text
                            ];
                        })
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

            $quizType = QuizType::findOrFail($request->quiz_type_id);
            
            // Résoudre le module quizable si spécifié
            $quizable = null;
            if ($request->quizable_type && $request->quizable_id) {
                $quizable = $this->resolveQuizable($request->quizable_type, $request->quizable_id);
                
                if (!$quizable) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Module quiz non trouvé'
                    ], 404);
                }

                // Vérifier la disponibilité si applicable
                if (!$quizable->isAvailable(Auth::user())) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce quiz n\'est pas disponible actuellement'
                    ], 403);
                }
            }            // Créer une instance de quiz avec la nouvelle structure polymorphique
            $quizInstance = QuizInstance::create([
                'user_id' => Auth::id(),
                'quiz_type_id' => $request->quiz_type_id,
                'quizable_type' => $quizable ? $request->quizable_type : null,
                'quizable_id' => $quizable ? $request->quizable_id : null,
                'quiz_mode' => $request->quiz_mode ?? ($quizable ? $quizable->getDefaultQuizMode() : 'standard'),
                'launch_date' => now(),
                'status' => 'started',
            ]);

            // Récupérer les questions en utilisant la nouvelle approche polymorphique
            $questions = collect();
            
            if ($quizable) {
                // Questions spécifiques au module
                $questionOptions = [
                    'quiz_type_id' => $request->quiz_type_id,
                    'quiz_mode' => $quizInstance->quiz_mode,
                    'user' => Auth::user()
                ];
                $questions = $quizable->getQuestions($questionOptions);
            } else {                // Fallback : questions générales par type de quiz
                $questionsQuery = Question::where('quiz_type_id', $request->quiz_type_id)
                    ->with(['choices' => function($query) {
                        $query->select('id', 'question_id', 'text'); // Utiliser le champ text
                    }]);

                // Backward compatibility pour chapter_id
                if ($request->chapter_id) {
                    $questionsQuery->where('chapter_id', $request->chapter_id);
                }

                $questions = $questionsQuery->inRandomOrder()
                    ->limit(10)
                    ->get();
            }            // Formatter les questions pour la réponse
            $formattedQuestions = $questions->map(function($question) {
                return [
                    'id' => $question->id,
                    'question_text' => $question->statement, // Utiliser le champ statement
                    'choices' => $question->choices->map(function($choice) {
                        return [
                            'id' => $choice->id,
                            'choice_text' => $choice->text // Utiliser le champ text
                        ];
                    })
                ];
            });

            // Préparer la réponse
            $responseData = [
                'quiz_instance_id' => $quizInstance->id,                'quiz_type' => [
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

            // Ajouter les informations du module si présent
            if ($quizable) {
                $responseData['quizable'] = [
                    'id' => $quizable->id,
                    'title' => $quizable->getQuizTitle(),
                    'description' => $quizable->getQuizDescription(),
                    'type' => $request->quizable_type
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Quiz démarré avec succès',
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du démarrage du quiz',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soumettre les réponses d'un quiz
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
                    'score' => $quizInstance->userQuizScore,
                    'answers' => $quizInstance->userAnswers->map(function($answer) {
                        return [
                            'question_id' => $answer->question_id,
                            'choice_id' => $answer->choice_id,
                            'is_correct' => $answer->is_correct,
                            'time_taken' => $answer->time_taken,                            'question' => [
                                'question_text' => $answer->question->statement // Utiliser le champ statement
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
     */
    private function resolveQuizable(string $quizableType, int $quizableId)
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

        return $modelClass::find($quizableId);
    }    /**
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
