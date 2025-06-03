<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Contrôleur pour la gestion de l'authentification API
 * 
 * Ce contrôleur gère l'inscription, la connexion, la déconnexion
 * et d'autres fonctionnalités liées à l'authentification.
 */
class AuthController extends Controller
{
    /**
     * Inscription d'un nouvel utilisateur
     * 
     * @param Request $request Requête contenant les données d'inscription
     * @return JsonResponse Réponse JSON avec le token d'authentification
     */
    public function register(Request $request)
    {
        try {
            // Validation avec des messages personnalisés
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'nom' => 'nullable|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'password_confirmation' => 'required|same:password',
            ], [
                'password.required' => 'Le mot de passe est obligatoire',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
                'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire',
                'password_confirmation.same' => 'La confirmation du mot de passe ne correspond pas',
                'email.unique' => 'Cette adresse email est déjà utilisée',
            ]);

            // Si la validation échoue, renvoyer les erreurs
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Créer l'utilisateur
            $user = User::create([
                'name' => $request->name,
                'nom' => $request->nom ?? $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'date_inscription' => now(),
            ]);
        } catch (\Exception $e) {
            // Répondre avec les détails de l'erreur pour le débogage
            return response()->json([
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage(),
                'debug' => [
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 201);
    }

    /**
     * Connexion d'un utilisateur
     * 
     * @param Request $request Requête contenant les identifiants de connexion
     * @return JsonResponse Réponse JSON avec le token d'authentification
     * @throws ValidationException Si les identifiants sont incorrects
     */
    public function login(Request $request)
    {
        // Valider les champs requis
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Tentative d'authentification
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        // Récupération de l'utilisateur et création du token
        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    /**
     * Déconnexion d'un utilisateur (révocation du token actuel)
     * 
     * @param Request $request Requête de l'utilisateur authentifié
     * @return JsonResponse Message de confirmation
     */
    public function logout(Request $request)
    {
        // Supprime uniquement le token utilisé pour cette requête
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès']);
    }

    /**
     * Récupérer les données de l'utilisateur connecté
     * 
     * @param Request $request Requête de l'utilisateur authentifié
     * @return JsonResponse Données de l'utilisateur connecté
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Déconnexion de tous les appareils (révocation de tous les tokens)
     * 
     * @param Request $request Requête de l'utilisateur authentifié
     * @return JsonResponse Message de confirmation
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Déconnecté de tous les appareils']);
    }
}
