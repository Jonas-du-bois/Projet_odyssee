<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizInstance;
use App\Models\UserAnswer;
use App\Models\UserQuizScore;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @group Quiz
 * 
 * API pour la gestion des sessions de quiz et des réponses
 */
class QuizController extends Controller
{
    /**
     * Démarrer une nouvelle session de quiz
     *
     * @param Request $request Données de la requête
     * @return JsonResponse
     */
    public function start(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quiz_type_id' => 'required|exists:quiz_types,id',
                'chapter_id' => 'nullable|exists:chapters,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Créer une instance de quiz
            $quizInstance = QuizInstance::create([
                'user_id' => Auth::id(),
                'quiz_type_id' => $request->quiz_type_id,
                'chapter_id' => $request->chapter_id,
                'status' => 'started',
                'started_at' => now(),
            ]);

            // Récupérer les questions pour ce quiz
            $questions = Question::where('quiz_type_id', $request->quiz_type_id)
                ->when($request->chapter_id, function($query) use ($request) {
                    return $query->where('chapter_id', $request->chapter_id);
                })
                ->with('choices')
                ->inRandomOrder()
                ->limit(10)  // Nombre de questions par quiz
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Quiz démarré avec succès',
                'data' => [
                    'quiz_instance_id' => $quizInstance->id,
                    'questions' => $questions
                ]
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
     * @param Request $request Données de la requête
     * @return JsonResponse
     */
    public function submitAnswers(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'quiz_instance_id' => 'required|exists:quiz_instances,id',
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|exists:questions,id',
                'answers.*.choice_id' => 'required|exists:choices,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier que le quiz appartient à l'utilisateur
            $quizInstance = QuizInstance::findOrFail($request->quiz_instance_id);
            
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

            // Enregistrer les réponses et calculer le score
            foreach ($request->answers as $answer) {
                $question = Question::with('choices')->findOrFail($answer['question_id']);
                $choice = $question->choices->find($answer['choice_id']);
                
                $isCorrect = $choice && $choice->is_correct;
                
                if ($isCorrect) {
                    $score++;
                }

                UserAnswer::create([
                    'user_id' => Auth::id(),
                    'quiz_instance_id' => $request->quiz_instance_id,
                    'question_id' => $answer['question_id'],
                    'choice_id' => $answer['choice_id'],
                    'is_correct' => $isCorrect,
                ]);
            }

            // Mettre à jour le statut du quiz
            $quizInstance->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Créer l'entrée de score
            $userQuizScore = UserQuizScore::create([
                'user_id' => Auth::id(),
                'quiz_instance_id' => $request->quiz_instance_id,
                'score' => $score,
                'total_questions' => $totalQuestions,
                'percentage' => ($totalQuestions > 0) ? ($score / $totalQuestions) * 100 : 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Réponses soumises avec succès',
                'data' => [
                    'score' => $score,
                    'total_questions' => $totalQuestions,
                    'percentage' => $userQuizScore->percentage,
                    'quiz_instance_id' => $request->quiz_instance_id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission des réponses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le résultat d'un quiz
     *
     * @param int $id Identifiant de l'instance de quiz
     * @return JsonResponse
     */
    public function getResult($id): JsonResponse
    {
        try {
            $quizInstance = QuizInstance::with(['userAnswers.question', 'userAnswers.choice', 'userQuizScore'])
                ->findOrFail($id);
            
            if ($quizInstance->user_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce quiz ne vous appartient pas'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_instance' => $quizInstance,
                    'answers' => $quizInstance->userAnswers,
                    'score' => $quizInstance->userQuizScore
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des résultats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
