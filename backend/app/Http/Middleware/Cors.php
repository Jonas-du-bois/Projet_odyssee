<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour gérer les requêtes CORS (Cross-Origin Resource Sharing)
 * 
 * Ce middleware permet de configurer les en-têtes CORS nécessaires pour permettre
 * au frontend de communiquer avec l'API backend depuis un domaine différent.
 */
class Cors
{
    /**
     * Traite une requête HTTP entrante et configure les en-têtes CORS
     *
     * @param  \Illuminate\Http\Request  $request  La requête HTTP
     * @param  \Closure  $next  Callback pour passer à l'étape suivante
     * @return \Symfony\Component\HttpFoundation\Response  La réponse HTTP
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Pour les requêtes OPTIONS (preflight)
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            $response = $next($request);
        }
        
        // Définir les origines autorisées
        $allowedOrigins = [
            'http://localhost:5173',        // Frontend Vue.js
            'http://127.0.0.1:5173',        // Frontend Vue.js (variante)
            'http://127.0.0.1:8000',        // Documentation Scribe
            'http://localhost:8000',        // Documentation Scribe (variante)
            'https://backend-breitling-league-e1d83468309e.herokuapp.com', // URL Heroku
            'null'                          // Pour les requêtes file://
        ];
        
        $origin = $request->headers->get('Origin');
        
        // En développement, on peut être plus permissif
        if (app()->environment('local')) {
            if ($origin) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
            } else {
                $response->headers->set('Access-Control-Allow-Origin', '*');
            }
        } else {
            // En production, on respecte la liste des origines autorisées
            if (in_array($origin, $allowedOrigins)) {
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
            } else if (!$origin) {
                // Si pas d'origine (requêtes API directes), on autorise sans credentials
                $response->headers->set('Access-Control-Allow-Origin', '*');
            } else {
                // Origine non autorisée, on refuse
                $response->headers->set('Access-Control-Allow-Origin', 'null');
            }
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept, Origin');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 heures
        
        return $response;
    }
}
