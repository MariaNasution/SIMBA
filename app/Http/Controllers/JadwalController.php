<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class JadwalController extends Controller
{
    public function index()
    {
        $user = session('user');
        $apiToken = session('api_token');
        $ta = session('ta');
        $sem_ta = session('sem_ta');

        try {
            // Ambil data jadwal dari API
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-jadwal-mhs', [
                    'nim' => $user['nim'],
                    'ta' => $ta,
                    'sem_ta' => $sem_ta,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();

                if (isset($responseData['data'])) {
                    // Data jadwal
                    $jadwalData = $responseData['data'];

                    // Format data untuk tabel jadwal
                    $jadwalFormatted = $this->formatJadwal($jadwalData);

                    return view('perkuliahan.jadwal', compact('jadwalFormatted'));
                }
            }

            Log::error('Jadwal API error:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data jadwal.']);
        } catch (\Exception $e) {
            Log::error('API Error:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan saat menghubungi API.']);
        }
    }

    private function formatJadwal($jadwalData)
    {
        $days = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
        $times = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

        // Membuat tabel kosong berdasarkan waktu dan hari
        $schedule = [];
        foreach ($times as $time) {
            $schedule[$time] = array_fill_keys(array_keys($days), []);
        }

        // Memasukkan jadwal ke tabel
        foreach ($jadwalData as $item) {
            $day = $item['hari'];
            $startTime = substr($item['waktu_mulai'], 0, 5); // Format "HH:MM"
            $matkul = $item['nama_mk'] . ' (' . $item['kelas'] . ')';

            if (isset($schedule[$startTime][$day])) {
                $schedule[$startTime][$day][] = $matkul;
            }
        }

        return compact('days', 'times', 'schedule');
    }
}
