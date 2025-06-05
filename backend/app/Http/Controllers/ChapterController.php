<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Chapitres
 * 
 * API pour gérer les chapitres et leurs unités de formation
 */
class ChapterController extends Controller
{
    /**
     * Lister tous les chapitres
     * 
     * Récupère la liste complète des chapitres avec leurs unités associées.
     *     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Introduction à Breitling",
     *       "description": "Découverte de l'histoire et des valeurs de Breitling",
     *       "order": 1,
     *       "units": [
     *         {
     *           "id": 1,
     *           "name": "Histoire de la marque",
     *           "content": "Contenu de formation...",
     *           "chapter_id": 1
     *         }
     *       ]
     *     }
     *   ]
     * }
     * 
     * @return JsonResponse
     */    public function index(): JsonResponse
    {
        try {
            $chapters = Chapter::with('units')->get();
            
            return response()->json([
                'success' => true,
                'data' => $chapters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des chapitres',
                'error' => $e->getMessage()
            ], 500);
        }
    }/**
     * Afficher un chapitre spécifique
     * 
     * Récupère les détails d'un chapitre particulier avec ses unités.
     * 
     * @urlParam id int required L'identifiant du chapitre. Example: 1
     *     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "title": "Introduction à Breitling",
     *     "description": "Découverte de l'histoire et des valeurs de Breitling",
     *     "order": 1,
     *     "units": [
     *       {
     *         "id": 1,
     *         "name": "Histoire de la marque",
     *         "content": "Contenu de formation...",
     *         "chapter_id": 1
     *       }
     *     ]
     *   }
     * }
     * 
     * @response 404 {
     *   "success": false,
     *   "message": "Chapitre non trouvé"
     * }
     * 
     * @param int $id Identifiant du chapitre
     * @return JsonResponse
     */    public function show($id): JsonResponse
    {
        try {
            $chapter = Chapter::with('units')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $chapter
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chapitre non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
