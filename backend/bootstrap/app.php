<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /**
         * Désactiver la vérification CSRF pour les routes API
         * Ceci est nécessaire car nous utilisons des tokens d'API et non des cookies de session
         */
        $middleware->validateCsrfTokens(except: ['api/*']);
        
        /**
         * Configuration des middlewares globaux pour l'API
         * - EnsureFrontendRequestsAreStateful: Support de l'authentification SPA
         * - Cors: Gestion des en-têtes Cross-Origin Resource Sharing
         */
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // \App\Http\Middleware\Cors::class,  // Temporairement désactivé pour debug
        ]);
        
        /**
         * Alias de middleware pour faciliter leur utilisation dans les routes
         */
        $middleware->alias([
            'cors' => \App\Http\Middleware\Cors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
