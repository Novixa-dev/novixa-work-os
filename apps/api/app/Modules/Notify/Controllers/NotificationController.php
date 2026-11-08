<?php

namespace App\Modules\Notify\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->take(30)
            ->get();

        $unreadCount = $user->notifications()->where('is_read', false)->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'تم تعيين الإشعار كمقروء',
            'unread_count' => $request->user()->notifications()->where('is_read', false)->count(),
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم تعيين جميع الإشعارات كمقروءة',
            'unread_count' => 0,
        ]);
    }
}
