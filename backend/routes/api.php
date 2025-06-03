<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * Application du middleware CORS à toutes les routes API
 */
Route::middleware('cors')->group(function () {
    /**
     * Routes d'authentification publiques - accessibles sans authentification
     */
    // Inscription d'un nouvel utilisateur
    Route::post('/register', [AuthController::class, 'register']);
    // Connexion d'un utilisateur
    Route::post('/login', [AuthController::class, 'login']);
    
    /**
     * Route pour gérer les requêtes OPTIONS (pre-flight) nécessaires pour CORS
     */
    Route::options('/{any}', function() {
        return response('', 200);
    })->where('any', '.*');
    
    /**
     * Route de test public pour vérifier que l'API fonctionne
     */
    Route::get('/test', function () {
        return response()->json([
            'message' => 'API fonctionne correctement',
            'timestamp' => now()->toDateTimeString()
        ]);
    });

    /**
     * Routes protégées - nécessitent une authentification via Sanctum
     */
    Route::middleware('auth:sanctum')->group(function () {
        // Récupérer les informations de l'utilisateur connecté
        Route::get('/me', [AuthController::class, 'me']);
        // Déconnexion (révocation du token actuel)
        Route::post('/logout', [AuthController::class, 'logout']);
        // Déconnexion de tous les appareils (révocation de tous les tokens)
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });
});
