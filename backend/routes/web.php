<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route de login pour éviter l'erreur de route manquante
Route::get('/login', function () {
    return response()->json(['message' => 'Use API login endpoint'], 404);
})->name('login');

// Routes manuelles pour la documentation Scribe (nécessaire sur Heroku)
Route::get('/docs', function () {
    return view('scribe.index');
})->name('scribe');

// Route pour la collection Postman
Route::get('/docs.postman', function () {
    $path = storage_path('app/private/scribe/collection.json');
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/json'
        ]);
    }
    abort(404);
})->name('scribe.postman');

// Route pour la spécification OpenAPI
Route::get('/docs.openapi', function () {
    $path = storage_path('app/private/scribe/openapi.yaml');
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/yaml'
        ]);
    }
    abort(404);
})->name('scribe.openapi');

// Route pour servir les assets CSS/JS de Scribe
Route::get('/docs/css/{file}', function ($file) {
    $path = public_path('vendor/scribe/css/' . $file);
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'text/css'
        ]);
    }
    abort(404);
});

Route::get('/docs/js/{file}', function ($file) {
    $path = public_path('vendor/scribe/js/' . $file);
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/javascript'
        ]);
    }
    abort(404);
});

// Route pour servir les autres assets de Scribe
Route::get('/vendor/scribe/{path}', function ($path) {
    $filePath = public_path('vendor/scribe/' . $path);
    if (file_exists($filePath)) {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
        ];
        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        return response()->file($filePath, [
            'Content-Type' => $contentType
        ]);
    }
    abort(404);
})->where('path', '.*');
