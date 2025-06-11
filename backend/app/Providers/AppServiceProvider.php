<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS en production (pour Heroku)
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        // Configuration des relations polymorphes pour éviter les problèmes d'autoload
        Relation::enforceMorphMap([
            'user' => 'App\Models\User',
            'discovery' => 'App\Models\Discovery',
            'event' => 'App\Models\Event',
            'weekly' => 'App\Models\Weekly',
            'novelty' => 'App\Models\Novelty',
            'reminder' => 'App\Models\Reminder',
            'unit' => 'App\Models\Unit',
        ]);
    }
}
