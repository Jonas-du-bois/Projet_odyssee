<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PostgreSQLOptimizationMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Optimisations spécifiques à PostgreSQL en production
        if (app()->isProduction() && config('database.default') === 'pgsql') {
            $this->optimizePostgreSQL();
        }

        $response = $next($request);

        // Log des requêtes lentes en développement
        if (app()->isLocal()) {
            $this->logSlowQueries();
        }

        return $response;
    }

    /**
     * Optimisations PostgreSQL pour la production
     */
    private function optimizePostgreSQL(): void
    {
        try {
            // Configuration optimale pour PostgreSQL sur Heroku
            DB::statement('SET work_mem = ?', ['4MB']);
            DB::statement('SET shared_buffers = ?', ['256MB']);
            DB::statement('SET effective_cache_size = ?', ['1GB']);
            
        } catch (\Exception $e) {
            // Les optimisations peuvent échouer selon les droits sur Heroku
            Log::debug('PostgreSQL optimization failed: ' . $e->getMessage());
        }
    }

    /**
     * Log des requêtes lentes pour le debugging
     */
    private function logSlowQueries(): void
    {
        DB::listen(function ($query) {
            if ($query->time > 100) { // Requêtes > 100ms
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms'
                ]);
            }
        });
    }
}
