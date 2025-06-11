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

// Route de debug pour les assets
Route::get('/debug-assets', function () {
    return response()->json([
        'app_url' => config('app.url'),
        'asset_url' => config('app.asset_url'),
        'css_url' => asset('/vendor/scribe/css/theme-default.style.css'),
        'js_url' => asset('/vendor/scribe/js/theme-default-5.2.1.js'),
        'app_env' => config('app.env'),
        'files_exist' => [
            'css' => file_exists(public_path('vendor/scribe/css/theme-default.style.css')),
            'js' => file_exists(public_path('vendor/scribe/js/theme-default-5.2.1.js'))
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

// Route manuelle pour forcer l'accès à la documentation Scribe si les routes automatiques ne fonctionnent pas
Route::get('/documentation', function () {
    try {
        return view('scribe.index');
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Documentation unavailable',
            'message' => $e->getMessage(),
            'debug' => [
                'view_exists' => file_exists(resource_path('views/scribe/index.blade.php')),
                'scribe_config' => [
                    'type' => config('scribe.type'),
                    'add_routes' => config('scribe.laravel.add_routes'),
                    'docs_url' => config('scribe.laravel.docs_url')
                ]
            ]
        ], 500);
    }
});

// Route de login pour éviter l'erreur de route manquante
Route::get('/login', function () {
    return response()->json(['message' => 'Use API login endpoint'], 404);
})->name('login');
