<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function markAllRead(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $updatedCount = $this->notificationService->markAllRead($user);

        return response()->json([
            'message'       => 'Semua notifikasi telah ditandai sebagai telah dibaca.',
            'updated_count' => $updatedCount,
        ]);
    }

    public function unread(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notifications = $this->notificationService->getUnreadNotifications($user);

        return response()->json([
            'unread_notifications' => $notifications,
        ]);
    }
}
