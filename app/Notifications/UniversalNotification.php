<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UniversalNotification extends Notification
{
    use Queueable;

    protected $message;
    protected $extraData;

    public function __construct(string $message, array $extraData = [])
    {
        $this->message   = $message;
        $this->extraData = $extraData;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message'    => $this->message,
            'extra_data' => $this->extraData,
        ];
    }
}
