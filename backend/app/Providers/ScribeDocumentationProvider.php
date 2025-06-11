<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ScribeDocumentationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Force l'enregistrement des routes de documentation Scribe en production
        if (config('scribe.laravel.add_routes', false)) {
            $this->registerScribeRoutes();
        }
    }

    /**
     * Enregistre manuellement les routes Scribe
     */
    protected function registerScribeRoutes(): void
    {
        $docsUrl = config('scribe.laravel.docs_url', '/docs');
        $middleware = config('scribe.laravel.middleware', []);

        Route::middleware($middleware)->group(function () use ($docsUrl) {
            // Route principale de la documentation
            Route::get($docsUrl, function () {
                try {
                    return view('scribe.index');
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Documentation not available',
                        'message' => $e->getMessage(),
                        'debug' => [
                            'view_path' => resource_path('views/scribe/index.blade.php'),
                            'view_exists' => file_exists(resource_path('views/scribe/index.blade.php')),
                            'app_env' => config('app.env')
                        ]
                    ], 500);
                }
            })->name('scribe');

            // Route pour la collection Postman
            Route::get($docsUrl . '.postman', function () {
                $path = storage_path('app/private/scribe/collection.json');
                if (file_exists($path)) {
                    return response()->file($path, [
                        'Content-Type' => 'application/json',
                        'Content-Disposition' => 'attachment; filename="collection.json"'
                    ]);
                }
                return response()->json(['error' => 'Postman collection not found'], 404);
            })->name('scribe.postman');

            // Route pour le spec OpenAPI
            Route::get($docsUrl . '.openapi', function () {
                $path = storage_path('app/private/scribe/openapi.yaml');
                if (file_exists($path)) {
                    return response()->file($path, [
                        'Content-Type' => 'application/x-yaml',
                        'Content-Disposition' => 'attachment; filename="openapi.yaml"'
                    ]);
                }
                return response()->json(['error' => 'OpenAPI spec not found'], 404);
            })->name('scribe.openapi');
        });
    }
}
