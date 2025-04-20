<?php

namespace App\Observers;

use App\Models\Perwalian;
use App\Models\Mahasiswa;
use App\Notifications\UniversalNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerwalianObserver
{
    public function created(Perwalian $perwalian): void
    {
        try {
            if ($perwalian->role !== 'mahasiswa') {
                Log::info('Skipping PerwalianObserver notification for non-mahasiswa role', [
                    'perwalian_id' => $perwalian->ID_Perwalian,
                    'role' => $perwalian->role,
                ]);
                return;
            }
            Log::Info("The value in Observer" . $perwalian['ID_Perwalian']);
            $mahasiswas = Mahasiswa::where('ID_Perwalian', $perwalian['ID_Perwalian'])->get();

            if ($mahasiswas->isNotEmpty()) {
                Log::info('Notification sending in observer disabled for testing', [
                    'perwalian_id' => $perwalian->ID_Perwalian,
                    'mahasiswa_count' => $mahasiswas->count(),
                ]);
            } else {
                Log::warning('No Mahasiswa found to notify for Perwalian', [
                    'perwalian_id' => $perwalian->ID_Perwalian,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for Perwalian creation (ID: {$perwalian->ID_Perwalian}): " . $e->getMessage());
        }
    }

    public function deleted(Perwalian $perwalian): void
    {
        try {
            if ($perwalian->role !== 'mahasiswa') {
                Log::info('Skipping PerwalianObserver notification for non-mahasiswa role', [
                    'perwalian_id' => $perwalian->ID_Perwalian,
                    'role' => $perwalian->role,
                ]);
                return;
            }

            $mahasiswas = Mahasiswa::where('ID_Perwalian', $perwalian->ID_Perwalian)->get();

            if ($mahasiswas->isNotEmpty()) {
                $date = Carbon::parse($perwalian->Tanggal)->translatedFormat('j F Y');
                $message = "Perwalian for your class {$perwalian->kelas} on {$date} by {$perwalian->nama} has been canceled.";
                $extraData = [
                    'link' => route('mahasiswa_perwalian'),
                    'type' => 'perwalian_canceled'
                ];

                foreach ($mahasiswas as $mahasiswa) {
                    try {
                        $mahasiswa->notify(new UniversalNotification($message, $extraData));
                        Log::info("Cancellation notification sent to Mahasiswa NIM {$mahasiswa->nim}: {$message}");
                    } catch (\Exception $e) {
                        Log::error("Failed to send cancellation notification to Mahasiswa NIM {$mahasiswa->nim}: {$e->getMessage()}", [
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            } else {
                Log::info('No Mahasiswa found to notify for Perwalian cancellation', [
                    'perwalian_id' => $perwalian->ID_Perwalian,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send cancellation notification for Perwalian deletion (ID: {$perwalian->ID_Perwalian}): " . $e->getMessage());
        }
    }
}