<?php

namespace App\Observers;

use App\Models\RequestKonseling;
use App\Models\Mahasiswa;
use App\Notifications\UniversalNotification;
use Illuminate\Support\Facades\Log;

class RequestKonselingObserver
{

    public function created(RequestKonseling $requestKonseling): void
    {
        $this->sendNotification($requestKonseling, 'diajukan');
    }

    protected function sendNotification(RequestKonseling $requestKonseling, $actionText)
    {
        $mahasiswa = Mahasiswa::where('nim', $requestKonseling->nim)->first();
        if (!$mahasiswa) {
            Log::error("Mahasiswa with NIM {$requestKonseling->nim} not found.");
            return;
        }
    
        $date = $requestKonseling->tanggal_pengajuan;
        $message   = "Anda telah {$actionText} untuk melakukan konseling pada {$date}.";
    
        $extraData = [
            'link' => route('mahasiswa_konseling'),
            'type' => 'konseling'
        ];
    
        try {
            // For Laravel built-in notifications, you might include the link inside the data payload.
            $mahasiswa->notify(new UniversalNotification($message, $extraData));
            Log::info("Notification sent to Mahasiswa NIM {$requestKonseling->student_nim}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification to Mahasiswa NIM {$requestKonseling->student_nim}. Error: " . $e->getMessage());
        }
    }
}
