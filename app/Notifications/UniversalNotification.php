<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UniversalNotification extends Notification
{
    use Queueable;

    private $message;
    private $extraData;

    public function __construct(string $message, array $extraData = [])
    {
        $this->message = $message;
        $this->extraData = $extraData;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['database']; // Add 'mail' if email notifications are desired
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->message,
            'id_perwalian' => $this->extraData['id_perwalian'] ?? null,
            'category' => $this->extraData['category'] ?? 'general',
            'action' => $this->extraData['action'] ?? null,
            'class' => $this->extraData['class'] ?? null,
            'date' => $this->extraData['date'] ?? null,
            'dosen_nip' => $this->extraData['dosen_nip'] ?? null,
            'link' => $this->extraData['link'] ?? null,
        ];
    }

    /**
     * Get the mail representation of the notification (optional).
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Perwalian Update')
            ->line($this->message);
        
        if (isset($this->extraData['link'])) {
            $mail->action('View Details', $this->extraData['link']);
        }
        
        return $mail->line('Thank you for using our application!');
    }
}