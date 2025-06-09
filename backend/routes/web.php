<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route de login pour éviter l'erreur de route manquante
Route::get('/login', function () {
    return response()->json(['message' => 'Use API login endpoint'], 404);
})->name('login');
