<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    Laravel\Sanctum\SanctumServiceProvider::class,
    // App\Providers\ScribeDocumentationProvider::class, // Désactivé car les routes Scribe fonctionnent maintenant
];
