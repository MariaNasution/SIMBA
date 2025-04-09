<?php

namespace App\Services;

use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Create a new notification.
     *
     * @param int    $userId  The ID of the user who will receive the notification.
     * @param string $type    The type or category of notification (e.g., 'perwalian', 'pelanggaran', 'konseling').
     * @param string $message The notification message.
     * @param array  $data    Any additional contextual data.
     * @return Notifikasi
     */
    public function createNotification($userId, $type, $message, array $data = [])
    {
        return Notifikasi::create([
            'user_id'  => $userId,
            'type'     => $type,
            'message'  => $message,
            'data'     => json_encode($data),
            'is_read'  => false,
        ]);
    }

    /**
     * Mark all notifications as read for the given user.
     *
     * @param int|null $userId If null, will use the authenticated user's ID.
     * @return int Number of notifications updated.
     */
    public function markAllRead($userId = null)
    {
        if (!$userId) {
            $user = Auth::user();
            $userId = $user ? $user->id : null;
        }

        if (!$userId) {
            return 0;
        }

        return Notifikasi::where('user_id', $userId)
                         ->where('is_read', false)
                         ->update(['is_read' => true]);
    }

    /**
     * Retrieve unread notifications for a given user.
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotifications($userId)
    {
        return Notifikasi::where('user_id', $userId)
                         ->where('is_read', false)
                         ->get();
    }
}