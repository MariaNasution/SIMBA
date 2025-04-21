<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
      
    }

    public function markAsRead(Request $request, $id)
    {
        $user = auth()->user();
        $success = $this->notificationService->markAsRead($user, $id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notification marked as read.' : 'Notification not found or already read.'
        ]);
    }

    public function markAllRead(Request $request)
    {
        $user = auth()->user();
        $this->notificationService->markAllRead($user);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.'
        ]);
    }
}