<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\QuizCompleted;
use App\Events\RankUpdated;
use App\Listeners\SynchronizeUserScore;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Événements spécifiques à Breitling League
        QuizCompleted::class => [
            SynchronizeUserScore::class,
        ],
        
        // Pour l'avenir : listeners pour RankUpdated
        RankUpdated::class => [
            // Ajouter des listeners pour les notifications de promotion par exemple
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
        
        // Configuration supplémentaire si nécessaire
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
