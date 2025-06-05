<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Notifications
 *
 * API pour gérer les notifications utilisateur
 */
class NotificationController extends Controller
{
    /**
     * Lister les notifications de l'utilisateur connecté
     *     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "title": "Nouveau quiz disponible",
     *       "message": "Un nouveau quiz sur l'aviation est maintenant disponible !",
     *       "type": "quiz",
     *       "read": false,
     *       "created_at": "2024-01-15T14:30:00.000000Z",
     *       "updated_at": "2024-01-15T14:30:00.000000Z"
     *     }
     *   ]
     * }
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $notifications = Notification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $notifications
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des notifications',
                'error' => $e->getMessage()
            ], 500);
        }
    }    /**
     * Marquer une notification comme lue
     *
     * @urlParam id int required L'ID de la notification à marquer comme lue. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Notification marquée comme lue"
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Notification non trouvée"
     * }
     *
     * @param int $id Identifiant de la notification
     * @return JsonResponse
     */
    public function markAsRead($id): JsonResponse
    {
        try {
            $notification = Notification::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
            
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification non trouvée'
                ], 404);
            }
              $notification->update([
                'read' => true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marquée comme lue'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Compter les notifications non lues
     *
     * @return JsonResponse
     */    public function unreadCount(): JsonResponse
    {
        try {
            $count = Notification::where('user_id', Auth::id())
                ->unread()
                ->count();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du comptage des notifications non lues',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
