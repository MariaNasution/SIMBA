<?php

namespace App\Observers;

use App\Models\Perwalian;
use App\Models\Mahasiswa;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class PerwalianObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function created(Perwalian $perwalian): void
    {
        $this->sendNotification($perwalian, 'dibuat');
    }

    protected function sendNotification(Perwalian $perwalian, string $actionText)
    {
        $mahasiswa = Mahasiswa::where('nim', $perwalian->nim)->first();
        if (!$mahasiswa) {
            Log::error("Mahasiswa with NIM {$perwalian->nim} not found.");
            return;
        }

        $message = "Jadwal Perwalian Anda telah {$actionText}.";
        $extraData = [
            'link' => route('mahasiswa_perwalian'),
            'type' => 'perwalian',
            'id_perwalian' => $perwalian->ID_Perwalian,
            // Add more fields if needed, e.g., 'class' => $perwalian->class, 'date' => $perwalian->date
        ];

        try {
            $this->notificationService->sendNotification($mahasiswa, $message, $extraData);
            Log::info("Notification sent to Mahasiswa NIM {$perwalian->nim}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification to Mahasiswa NIM {$perwalian->nim}. Error: " . $e->getMessage());
        }
    }
}