<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function markAsRead(Request $request, $id)
    {
        $user = auth()->user() ?? session('user');
        if (!$user || !isset($user['role'])) {
            Log::error('No user or role found in markAsRead', ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        try {
            $success = $this->notificationService->markAsRead($user, $id);
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Notification marked as read.' : 'Notification not found or already read.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markAllRead(Request $request)
    {
        $user = auth()->user() ?? session('user');
        if (!$user || !isset($user['role'])) {
            Log::error('No user or role found in markAllRead', ['user' => $user]);
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        try {
            $this->notificationService->markAllRead($user);
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read: ' . $e->getMessage()
            ], 500);
        }
    }
}