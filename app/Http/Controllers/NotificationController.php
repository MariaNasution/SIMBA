<?php

namespace App\Observers;

use App\Models\Perwalian;
use App\Models\Mahasiswa;
use App\Notifications\UniversalNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerwalianObserver
{
    public function created(Perwalian $perwalian)
    {
        $this->sendNotification($perwalian, 'ditambahkan');
    }

    public function deleted(Perwalian $perwalian)
    {
        $this->deleteNotifications($perwalian);
    }

    protected function sendNotification(Perwalian $perwalian, $actionText)
    {
        $mahasiswas = Mahasiswa::where('ID_Perwalian', $perwalian->ID_Perwalian)->get();

        if ($mahasiswas->isEmpty()) {
            Log::warning('No Mahasiswa found to notify for Perwalian', [
                'perwalian_id' => $perwalian->ID_Perwalian,
            ]);
            return;
        }

        $date = Carbon::parse($perwalian->Tanggal)->translatedFormat('j F Y');
        $message = "Perwalian untuk kelas {$perwalian->kelas} pada {$date} telah {$actionText}.";
        $destinationRoute = route('mahasiswa_perwalian');

        try {
            foreach ($mahasiswas as $mahasiswa) {
                $mahasiswa->notify(new UniversalNotification($message, ['link' => $destinationRoute, 'type' => 'perwalian']));
                Log::info("Notification sent to Mahasiswa NIM {$mahasiswa->nim}: {$message}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for Perwalian (ID: {$perwalian->ID_Perwalian}): " . $e->getMessage());
        }
    }

    protected function deleteNotifications(Perwalian $perwalian)
    {
        $mahasiswas = Mahasiswa::where('ID_Perwalian', $perwalian->ID_Perwalian)->get();

        if ($mahasiswas->isEmpty()) {
            Log::info('No Mahasiswa notifications to delete for Perwalian', [
                'perwalian_id' => $perwalian->ID_Perwalian,
            ]);
            return;
        }

        try {
            foreach ($mahasiswas as $mahasiswa) {
                $deletedCount = $mahasiswa->notifications()
                    ->where('data->type', 'perwalian')
                    ->where('data->link', route('mahasiswa_perwalian'))
                    ->delete();

                Log::info("Deleted notifications for Mahasiswa NIM {$mahasiswa->nim} due to Perwalian deletion", [
                    'perwalian_id' => $perwalian->ID_Perwalian,
                    'deleted_count' => $deletedCount,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete notifications for Perwalian (ID: {$perwalian->ID_Perwalian}): " . $e->getMessage());
        }
    }
}