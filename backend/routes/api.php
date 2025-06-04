<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NoveltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WeeklyController;
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
     * Routes protégées - nécessitent une authentification via Sanctum
     */
    Route::middleware('auth:sanctum')->group(function () {
        // Routes AuthController
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        
        // Routes ChapterController
        Route::get('/chapters', [ChapterController::class, 'index']);
        Route::get('/chapters/{id}', [ChapterController::class, 'show']);
        
        // Routes QuizController
        Route::post('/quiz/start', [QuizController::class, 'start']);
        Route::post('/quiz/submit', [QuizController::class, 'submitAnswers']);
        Route::get('/quiz/{id}/result', [QuizController::class, 'getResult']);
          // Routes WeeklyController - User routes
        Route::get('/weekly', [WeeklyController::class, 'index']);
        Route::post('/weekly/{id}/claim', [WeeklyController::class, 'claimTicket']);
        Route::get('/weekly/series', [WeeklyController::class, 'getSeries']);
        Route::get('/weekly/tickets', [WeeklyController::class, 'getTickets']);
        
        // Routes ProgressController
        Route::get('/progress', [ProgressController::class, 'getProgress']);
        Route::get('/progress/rank', [ProgressController::class, 'getRang']);
        Route::get('/progress/history', [ProgressController::class, 'getUserQuizHistory']);
        Route::get('/progress/wrap', [ProgressController::class, 'getWrapData']);
        
        // Routes TicketController
        Route::get('/tickets', [TicketController::class, 'listTickets']);
        Route::post('/tickets/bonus', [TicketController::class, 'claimBonus']);
        Route::get('/tickets/stats', [TicketController::class, 'getStats']);
        
        // Routes NotificationController
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);        // Routes UserController (optionnel)
        Route::get('/profile', [UserController::class, 'show']);
        Route::put('/profile', [UserController::class, 'update']);
          // Routes NoveltyController - User accessible
        Route::get('/novelties/{id}', [NoveltyController::class, 'show']);
          // Routes ReminderController - User accessible
        Route::get('/reminders', [ReminderController::class, 'index']);
        Route::get('/reminders/{id}', [ReminderController::class, 'show']);
        
        // Routes EventController - User accessible
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{id}/units', [EventController::class, 'units']);
        
        // Routes protégées pour les administrateurs
        Route::middleware('admin')->group(function () {
            // Routes DiscoveryController (admin)
            Route::get('/discoveries', [DiscoveryController::class, 'index']);
            Route::get('/discoveries/{id}', [DiscoveryController::class, 'show']);
            Route::post('/discoveries', [DiscoveryController::class, 'store']);
            Route::put('/discoveries/{id}', [DiscoveryController::class, 'update']);
            Route::delete('/discoveries/{id}', [DiscoveryController::class, 'destroy']);
            
            // Routes WeeklyController - Admin routes
            Route::post('/weekly', [WeeklyController::class, 'store']);
            Route::put('/weekly/{id}', [WeeklyController::class, 'update']);
            Route::delete('/weekly/{id}', [WeeklyController::class, 'destroy']);
              // Routes ReminderController (admin)
            Route::post('/reminders', [ReminderController::class, 'store']);
            Route::put('/reminders/{id}', [ReminderController::class, 'update']);
            Route::delete('/reminders/{id}', [ReminderController::class, 'destroy']);
            
            // Routes EventController (admin)
            Route::get('/events', [EventController::class, 'index']);
            Route::post('/events', [EventController::class, 'store']);
            Route::put('/events/{id}', [EventController::class, 'update']);
            Route::delete('/events/{id}', [EventController::class, 'destroy']);            // Routes NoveltyController (admin)
            Route::get('/novelties', [NoveltyController::class, 'index']);
            Route::post('/novelties', [NoveltyController::class, 'store']);
            Route::put('/novelties/{id}', [NoveltyController::class, 'update']);
            Route::delete('/novelties/{id}', [NoveltyController::class, 'destroy']);
        });
    });
});

