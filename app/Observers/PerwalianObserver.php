<?php

namespace App\Observers;

use App\Models\Perwalian;
use App\Models\Mahasiswa;
use App\Notifications\UniversalNotification;
use Illuminate\Support\Facades\Log;

class PerwalianObserver
{
    public function created(Perwalian $perwalian): void
    {
        try {
            // Determine the related Mahasiswa records based on the Perwalian
            $mahasiswas = Mahasiswa::where('ID_Perwalian', $perwalian->ID_Perwalian)->get();

            if ($mahasiswas->isEmpty() && $perwalian->role === 'dosen') {
                // For dosen role (from KemahasiswaanPerwalianController), notify the dosen
                $dosen = \App\Models\Dosen::where('nip', $perwalian->ID_Dosen_Wali)->first();
                if ($dosen) {
                    $date = Carbon::parse($perwalian->Tanggal)->translatedFormat('j F Y');
                    $message = "Perwalian scheduled for you (Keterangan: {$perwalian->keterangan}) - {$date}";
                    $extraData = [
                        'link' => route('dosen.perwalian'),
                        'type' => 'perwalian'
                    ];
                    $dosen->notify(new UniversalNotification($message, $extraData));
                    Log::info("Notification sent to Dosen NIP {$dosen->nip}: {$message}");
                }
            } elseif ($mahasiswas->isNotEmpty()) {
                // For mahasiswa role (from SetPerwalianController), notify all related students
                foreach ($mahasiswas as $mahasiswa) {
                    $date = Carbon::parse($perwalian->Tanggal)->translatedFormat('j F Y');
                    $message = "Perwalian scheduled for your class {$perwalian->kelas} on {$date}.";
                    $extraData = [
                        'link' => route('mahasiswa_perwalian'),
                        'type' => 'perwalian'
                    ];
                    $mahasiswa->notify(new UniversalNotification($message, $extraData));
                    Log::info("Notification sent to Mahasiswa NIM {$mahasiswa->nim}: {$message}");
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to send notification for Perwalian creation (ID: {$perwalian->ID_Perwalian}): " . $e->getMessage());
        }
    }

    public function deleted(Perwalian $perwalian): void
    {
        try {
            // Determine the related notifiable entities (Mahasiswa or Dosen)
            $mahasiswas = Mahasiswa::where('ID_Perwalian', $perwalian->ID_Perwalian)->get();
            $dosen = \App\Models\Dosen::where('nip', $perwalian->ID_Dosen_Wali)->first();

            if ($mahasiswas->isNotEmpty()) {
                // For mahasiswa role, mark notifications as read for all related students
                foreach ($mahasiswas as $mahasiswa) {
                    $mahasiswa->notifications()
                        ->where('data->type', 'perwalian')
                        ->where('data->extra_data->link', route('mahasiswa_perwalian'))
                        ->update(['read_at' => now()]);
                    Log::info("Marked notifications as read for Mahasiswa NIM {$mahasiswa->nim} due to Perwalian deletion (ID: {$perwalian->ID_Perwalian})");
                }
            } elseif ($dosen) {
                // For dosen role, mark notifications as read
                $dosen->notifications()
                    ->where('data->type', 'perwalian')
                    ->where('data->extra_data->link', route('dosen.perwalian'))
                    ->update(['read_at' => now()]);
                Log::info("Marked notifications as read for Dosen NIP {$dosen->nip} due to Perwalian deletion (ID: {$perwalian->ID_Perwalian})");
            }
        } catch (\Exception $e) {
            Log::error("Failed to handle notifications for Perwalian deletion (ID: {$perwalian->ID_Perwalian}): " . $e->getMessage());
        }
    }
}