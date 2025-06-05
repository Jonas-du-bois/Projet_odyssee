<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * @group User Management
 *
 * API pour gérer le profil utilisateur
 */
class UserController extends Controller
{
    /**
     * Afficher le profil de l'utilisateur connecté
     *     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     "email_verified_at": "2024-01-01T00:00:00.000000Z",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     *
     * @return JsonResponse
     */public function show(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            // Exclure les champs sensibles
            $user->makeHidden(['password']);
            
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Mettre à jour le profil de l'utilisateur connecté
     *
     * @bodyParam name string Le nom de l'utilisateur. Example: John Doe
     * @bodyParam email string L'email de l'utilisateur. Example: john.doe@example.com
     * @bodyParam password string Le nouveau mot de passe (optionnel). Example: newpassword123
     * @bodyParam password_confirmation string Confirmation du nouveau mot de passe. Example: newpassword123
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Profil mis à jour avec succès",
     *   "data": {     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     "email_verified_at": "2024-01-01T00:00:00.000000Z",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T14:30:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "errors": {
     *     "email": ["L'email est déjà utilisé par un autre utilisateur."],
     *     "password": ["Les mots de passe ne correspondent pas."]
     *   }
     * }
     *
     * @param Request $request Données de la requête
     * @return JsonResponse
     */public function update(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'email' => 'email|unique:users,email,' . $user->id,
                'current_password' => 'required_with:password|string',
                'password' => 'string|min:8|confirmed',
                'avatar' => 'nullable|string',
                'job_title' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'preferences' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier le mot de passe actuel si un nouveau mot de passe est fourni
            if ($request->filled('password')) {
                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le mot de passe actuel est incorrect'
                    ], 422);
                }
                
                $user->password = Hash::make($request->password);
            }
            
            // Mettre à jour les champs de base
            if ($request->filled('name')) {
                $user->name = $request->name;
            }
            
            if ($request->filled('email')) {
                $user->email = $request->email;
            }
            
            if ($request->filled('avatar')) {
                $user->avatar = $request->avatar;
            }
            
            if ($request->filled('job_title')) {
                $user->job_title = $request->job_title;
            }
            
            if ($request->filled('department')) {
                $user->department = $request->department;
            }
            
            // Mettre à jour les préférences utilisateur
            if ($request->has('preferences')) {
                $user->preferences = $request->preferences;
            }
            
            $user->save();
            
            // Exclure les champs sensibles
            $user->makeHidden(['password']);
            
            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => $user
            ]);        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher le profil public d'un autre utilisateur
     *
     * @urlParam id int required L'ID de l'utilisateur. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "rank": {"name": "Silver", "level": 2},
     *     "total_points": 1500,
     *     "registration_date": "2024-01-15",
     *     "quiz_count": 25
     *   }
     * }
     */    public function showPublicProfile($id): JsonResponse
    {
        try {
            $user = User::with(['rank', 'scores'])->find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }

            $totalScore = $user->getTotalPointsWithFallback();
            $quizCount = $user->quizInstances()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'rank' => $user->rank ? [
                        'name' => $user->rank->name,
                        'level' => $user->rank->level
                    ] : null,
                    'total_points' => $totalScore,
                    'registration_date' => $user->registration_date,
                    'quiz_count' => $quizCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
