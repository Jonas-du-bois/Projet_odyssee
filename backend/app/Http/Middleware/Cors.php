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
        
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173'); // URL du frontend Vue.js
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, X-Token-Auth, Authorization, Accept');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 heures
        
        return $response;
    }
}
