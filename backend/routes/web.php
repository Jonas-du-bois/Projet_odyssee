<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route de test pour la documentation
Route::get('/docs-test', function () {
    return response()->json([
        'message' => 'Documentation route test',
        'scribe_config' => [
            'add_routes' => config('scribe.laravel.add_routes'),
            'docs_url' => config('scribe.laravel.docs_url'),
            'middleware' => config('scribe.laravel.middleware')
        ]
    ]);
});

// Route alternative pour la documentation en cas de problème
Route::get('/docs-alt', function () {
    try {
        return view('scribe.index');
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Documentation view not found',
            'message' => $e->getMessage(),
            'scribe_views_exist' => file_exists(resource_path('views/scribe/index.blade.php'))
        ]);
    }
});

// Route de login pour éviter l'erreur de route manquante
Route::get('/login', function () {
    return response()->json(['message' => 'Use API login endpoint'], 404);
})->name('login');
