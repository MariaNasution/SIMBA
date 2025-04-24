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

    public function created(Perwalian $perwalian)
    {
        $this->sendNotification($perwalian, 'dijadwalkan');
    }

    public function updated(Perwalian $perwalian)
    {
        $this->sendNotification($perwalian, 'diperbarui');
    }

    public function deleted(Perwalian $perwalian)
    {
        $this->sendNotification($perwalian, 'dibatalkan');
    }

    protected function sendNotification(Perwalian $perwalian, $actionText)
    {
        // Fetch all Mahasiswa associated with this Perwalian
        $mahasiswaList = Mahasiswa::where('kelas', $perwalian->kelas)->get();
        if ($mahasiswaList->isEmpty()) {
            Log::error("No Mahasiswa found for Perwalian kelas {$perwalian->kelas}.");
            return;
        }

        $message = "Perwalian kelas {$perwalian->kelas} telah {$actionText} pada " .
                   $perwalian->Tanggal->translatedFormat('l, d F Y');

        // Determine destination route based on action
        $destinationRoute = route('mahasiswa_perwalian');

        try {
            foreach ($mahasiswaList as $mahasiswa) {
                $this->notificationService->sendNotification(
                    $mahasiswa,
                    $message,
                    [
                        'link' => $destinationRoute,
                        'type' => 'perwalian'
                    ]
                );
                Log::info("Notification sent to Mahasiswa NIM {$mahasiswa->nim}: {$message}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for Perwalian kelas {$perwalian->kelas}. Error: " . $e->getMessage());
        }
    }
}