<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Pengumuman;
use App\Models\Mahasiswa;
use App\Models\Notifikasi; // Pastikan sudah import model Notifikasi
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MahasiswaHomeController extends Controller
{
    public function index()
    {
        // Ambil user dari session (menyesuaikan dengan session-based auth)
        $user = session('user');

        // Validasi bahwa user terautentikasi dan berperan sebagai mahasiswa
        if (!$user || !isset($user['role']) || $user['role'] !== 'mahasiswa') {
            Log::error('User not authenticated or not a mahasiswa', ['user' => $user]);
            return redirect()->route('login')->withErrors(['error' => 'Please log in as a mahasiswa.']);
        }

        // Cari data mahasiswa berdasarkan username dari session
        $mahasiswa = Mahasiswa::where('username', $user['username'])->first();
        if (!$mahasiswa) {
            Log::error('No mahasiswa record found for user', ['username' => $user['username']]);
            return redirect()->route('login')->withErrors(['error' => 'Mahasiswa data not found.']);
        }
        $nim = $mahasiswa->nim;
        $apiToken = session('api_token');

        // Inisialisasi nilai default untuk API
        $studentData = [];
        $data = [];
        $ipSemester = [];
        $labels = [];
        $values = [];

        // Ambil data student (jika memungkinkan)
        try {
            $studentResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->asForm()
                ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                    'nim' => $nim,
                ]);

            if ($studentResponse->successful()) {
                $studentData = $studentResponse->json()['data'] ?? [];
                // Simpan sem_ta dan ta ke session jika ada
                session([
                    'sem_ta' => $studentData['sem_ta'] ?? null,
                    'ta' => $studentData['ta'] ?? null,
                ]);
            } else {
                Log::error('Student API request failed', ['status' => $studentResponse->status()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception on fetching student data:', ['message' => $e->getMessage()]);
        }

        // Ambil data penilaian
        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->asForm()
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $nim,
                ]);
            Log::info('Respons API mentah:', ['body' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();
                $ipSemester = $data['IP Semester'] ?? [];

                // Urutkan IP Semester berdasarkan tahun akademik (ta) dan semester (sem)
                uasort($ipSemester, function ($a, $b) {
                    if ($a['ta'] === $b['ta']) {
                        return $a['sem'] <=> $b['sem'];
                    }
                    return $a['ta'] <=> $b['ta'];
                });

                // Siapkan label dan nilai untuk chart
                foreach ($ipSemester as $details) {
                    $labels[] = "TA {$details['ta']} - Semester {$details['sem']}";
                    $values[] = is_numeric($details['ip_semester']) ? (float) $details['ip_semester'] : 0;
                }
            } else {
                Log::error('Penilaian API request failed', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception on fetching penilaian data:', ['message' => $e->getMessage()]);
        }

        // Ambil data pengumuman dan kalender akademik
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        $akademik = Calendar::where('type', 'akademik')->latest()->first();
        $bem = Calendar::where('type', 'bem')->latest()->first();

        // --- Ambil data notifikasi untuk mahasiswa ---
        $notifications = Notifikasi::where('nim', $nim)
            ->orderBy('created_at', 'desc')
            ->get();

        // Tetap render halaman dengan data yang ada (meski mungkin data chart kosong)
        return view('beranda.homeMahasiswa', compact('labels', 'values', 'pengumuman', 'akademik', 'bem', 'notifications', 'mahasiswa'));
    }

    public function show($id)
    {
        // Ambil pengumuman berdasarkan ID
        $pengumuman = Pengumuman::findOrFail($id);

        // Kembalikan ke view detail pengumuman
        return view('beranda.detailpengumuman', compact('pengumuman'));
    }
}