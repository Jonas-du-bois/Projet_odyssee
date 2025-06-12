<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\DiscoveryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NoveltyController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RankController;
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
    
    // Routes de debug temporaires
    Route::get('/debug/database-status', [App\Http\Controllers\DebugController::class, 'databaseStatus']);
    Route::post('/debug/seed-questions', [App\Http\Controllers\DebugController::class, 'seedQuestions']);
    
    /**
     * Route pour gérer les requêtes OPTIONS (pre-flight) nécessaires pour CORS
     */    Route::options('/{any}', function() {
        return response('', 200);
    })->where('any', '.*');

    /**
     * Routes protégées - nécessitent une authentification via Sanctum
     */
    Route::middleware('auth:sanctum')->group(function () {
        // Routes AuthController
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/user', [AuthController::class, 'me']); // Alias pour /user
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
          // Routes ChapterController
        Route::get('/chapters', [ChapterController::class, 'index']);
        Route::get('/chapters/{id}', [ChapterController::class, 'show']);
        
        // Routes QuizController
        Route::get('/quiz/types', [QuizController::class, 'getQuizTypes']);
        Route::get('/quiz/instances', [QuizController::class, 'getUserQuizInstances']);
        Route::get('/quiz/instance/{id}', [QuizController::class, 'getInstance']);
        Route::get('/quiz/stats', [QuizController::class, 'getUserStats']);
        Route::post('/quiz/start', [QuizController::class, 'start']);
        Route::post('/quiz/submit', [QuizController::class, 'submitAnswers']);        Route::get('/quiz/{id}/result', [QuizController::class, 'getResult']);
        
        // Routes WeeklyController - User routes
        Route::get('/weekly', [WeeklyController::class, 'index']);
        Route::post('/weekly/{id}/claim', [WeeklyController::class, 'claimTicket']);
        Route::get('/weekly/series', [WeeklyController::class, 'getSeries']);
        Route::get('/weekly/tickets', [WeeklyController::class, 'getTickets']);
        
        // Routes ProgressController
        Route::get('/progress', [ProgressController::class, 'getProgress']);
        Route::get('/progress/rank', [ProgressController::class, 'getRang']);
        Route::get('/progress/history', [ProgressController::class, 'getUserQuizHistory']);
        Route::get('/progress/wrap', [ProgressController::class, 'getWrapData']);        Route::get('/leaderboard', [ProgressController::class, 'getLeaderboard']);
        
        // Routes RankController - Documentation et informations sur les rangs
        Route::get('/ranks', [RankController::class, 'index']);
        Route::get('/ranks/adjacent/user', [RankController::class, 'getAdjacentRanks']);
        Route::get('/ranks/minimum-points', [RankController::class, 'getMinimumPoints']);
        Route::get('/ranks/user/progression', [RankController::class, 'getUserProgression']);
        Route::get('/ranks/statistics', [RankController::class, 'getStatistics']);
        Route::get('/ranks/{id}', [RankController::class, 'show']);
        
        // Routes TicketController
        Route::get('/tickets', [TicketController::class, 'listTickets']);
        Route::post('/tickets/bonus', [TicketController::class, 'claimBonus']);        Route::get('/tickets/stats', [TicketController::class, 'getStats']);
        
        // Routes NotificationController
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        
        // Routes UserController
        Route::get('/profile', [UserController::class, 'show']);
        Route::put('/profile', [UserController::class, 'update']);
        Route::get('/users/{id}/profile', [UserController::class, 'showPublicProfile']);
        
        // Routes DiscoveryController - Accessibles à tous
        Route::get('/discoveries', [DiscoveryController::class, 'index']);
        Route::get('/discoveries/{id}', [DiscoveryController::class, 'show']);
        
        // Routes NoveltyController - Accessibles à tous
        Route::get('/novelties', [NoveltyController::class, 'index']);
        Route::get('/novelties/{id}', [NoveltyController::class, 'show']);
        
        // Routes ReminderController - Accessibles à tous
        Route::get('/reminders', [ReminderController::class, 'index']);        Route::get('/reminders/{id}', [ReminderController::class, 'show']);
        
        // Routes EventController - Accessibles à tous
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{id}/units', [EventController::class, 'units']);
    });
});

