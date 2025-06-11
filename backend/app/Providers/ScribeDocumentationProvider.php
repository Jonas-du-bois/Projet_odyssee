<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ScribeDocumentationProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Force l'enregistrement des routes de documentation Scribe en production
        if (config('scribe.laravel.add_routes', false)) {
            $this->registerScribeRoutes();
        }

        // S'assurer que les assets Scribe sont publiés
        if (config('app.env') === 'production') {
            $this->ensureScribeAssetsExist();
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

    /**
     * S'assurer que les assets Scribe existent en production
     */
    protected function ensureScribeAssetsExist(): void
    {
        $assetsPath = public_path('vendor/scribe');
        
        if (!is_dir($assetsPath)) {
            // Si le dossier n'existe pas, on tente de le créer
            mkdir($assetsPath, 0755, true);
        }
        
        // Vérifier si les assets principaux existent
        $cssPath = $assetsPath . '/css/theme-default.style.css';
        $jsPath = $assetsPath . '/js/theme-default-5.2.1.js';
        
        if (!file_exists($cssPath) || !file_exists($jsPath)) {
            // Regenerer les assets Scribe si nécessaire
            try {
                Artisan::call('scribe:generate');
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas bloquer l'application
                Log::warning('Could not regenerate Scribe assets: ' . $e->getMessage());
            }
        }
    }
}
