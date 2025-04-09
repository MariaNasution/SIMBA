<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificationService;

class NotifikasiController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Mark all notifications as read for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllRead(Request $request)
    {
        // Use the authenticated user rather than relying on a 'nim' input.
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $updatedCount = $this->notificationService->markAllRead($user->id);

        return response()->json([
            'message' => 'Notifications have been marked as read.',
            'updated_count' => $updatedCount,
        ]);
    }
}
