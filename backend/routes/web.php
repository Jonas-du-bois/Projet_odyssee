<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route de login pour éviter l'erreur de route manquante
Route::get('/login', function () {
    return response()->json(['message' => 'Use API login endpoint'], 404);
})->name('login');

// Route manuelle pour la documentation Scribe (nécessaire sur Heroku)
Route::get('/docs', function () {
    return view('scribe.index');
})->name('scribe');

// Route pour servir les assets CSS/JS de Scribe
Route::get('/docs/css/{file}', function ($file) {
    $path = public_path('docs/css/' . $file);
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'text/css'
        ]);
    }
    abort(404);
});

Route::get('/docs/js/{file}', function ($file) {
    $path = public_path('docs/js/' . $file);
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/javascript'
        ]);
    }
    abort(404);
});
