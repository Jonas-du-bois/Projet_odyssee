<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Events\QuizCompleted;
use App\Listeners\SynchronizeUserScore;

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
        // Configuration des relations polymorphes pour éviter les problèmes d'autoload
        Relation::enforceMorphMap([
            'discovery' => 'App\Models\Discovery',
            'event' => 'App\Models\Event',
            'weekly' => 'App\Models\Weekly',
            'novelty' => 'App\Models\Novelty',
            'reminder' => 'App\Models\Reminder',
            'unit' => 'App\Models\Unit',
        ]);

        // Enregistrer l'écouteur pour l'événement QuizCompleted
        Event::listen(
            QuizCompleted::class,
            SynchronizeUserScore::class,
        );
    }
}
