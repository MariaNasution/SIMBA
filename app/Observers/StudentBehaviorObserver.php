<?php

namespace App\Observers;

use App\Models\StudentBehavior;
use App\Models\Mahasiswa;
use App\Notifications\UniversalNotification;
use Illuminate\Support\Facades\Log;

class StudentBehaviorObserver
{
    public function created(StudentBehavior $behavior)
    {
        $this->sendNotification($behavior, 'ditambahkan');
    }
    public function updated(StudentBehavior $behavior)
    {
        $this->sendNotification($behavior, 'diperbarui');
    }

    public function deleted(StudentBehavior $behavior)
    {
        $this->sendNotification($behavior, 'dihapus');
    }

    protected function sendNotification(StudentBehavior $behavior, $actionText)
    {
        $mahasiswa = Mahasiswa::where('nim', $behavior->student_nim)->first();
        if (!$mahasiswa) {
            Log::error("Mahasiswa with NIM {$behavior->student_nim} not found.");
            return;
        }
    
        $typeLabel = $behavior->type === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik';
        $message   = "Data {$typeLabel} telah {$actionText}.";
    
        // Determine a destination route based on behavior type or other criteria
        $destinationRoute = route('mahasiswa_perwalian'); // default route
        // You could add conditions to adjust this route as needed
    
        try {
            // For Laravel built-in notifications, you might include the link inside the data payload.
            $mahasiswa->notify(new UniversalNotification($message, ['link' => $destinationRoute]));
            Log::info("Notification sent to Mahasiswa NIM {$behavior->student_nim}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send notification to Mahasiswa NIM {$behavior->student_nim}. Error: " . $e->getMessage());
        }
    }   
}