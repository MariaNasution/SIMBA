<?php

namespace App\Services;

use App\Notifications\UniversalNotification;

class NotificationService
{
    public function sendNotification($notifiable, string $message, array $extraData = [])
    {
        $notifiable->notify(new UniversalNotification($message, $extraData));
    }

    public function markAllRead($notifiable)
    {
        return $notifiable->unreadNotifications()->update(['read_at' => now()]);
    }

    public function markAsRead($notifiable, $notificationId)
    {
        $notification = $notifiable->notifications()->where('id', $notificationId)->first();
        if ($notification && is_null($notification->read_at)) {
            $notification->update(['read_at' => now()]);
            return true;
        }
        return false;
    }

    public function getUnreadNotifications($notifiable)
    {
        return $notifiable->unreadNotifications;
    }
}