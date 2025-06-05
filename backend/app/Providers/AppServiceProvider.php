<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
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
        // Enregistrer l'écouteur pour l'événement QuizCompleted
        Event::listen(
            QuizCompleted::class,
            SynchronizeUserScore::class,
        );
    }
}
