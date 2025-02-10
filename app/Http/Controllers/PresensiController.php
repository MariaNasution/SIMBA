<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PresensiController extends Controller
{
    public function index()
    {
        $user = session('user');
        $nim = $user['nim'] ?? null;
        $apiToken = session('api_token');

        if (!$nim) {
            return redirect()->route('login')->withErrors(['error' => 'NIM tidak ditemukan.']);
        }

        try {
            // API Presensi
            $presensiResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-presensi-by-nim', [
                    'nim' => $nim,
                ]);

            // API Nilai Akhir
            $nilaiResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/nilai-akhir', [
                    'nim' => $nim,
                ]);

            if ($presensiResponse->successful() && $nilaiResponse->successful()) {
                $presensiData = $presensiResponse->json()['data'] ?? [];
                $nilaiData = $nilaiResponse->json()['data'] ?? [];

                // Map SKS dari API Nilai Akhir
                $sksMap = collect($nilaiData)->keyBy('kode_mk')->map(function ($item) {
                    return $item['sks'];
                });

                // Proses data presensi dengan SKS
                $attendances = [];
                foreach ($presensiData as $course) {
                    $totalSessions = count($course['presensi']);
                    $attendedSessions = count(array_filter($course['presensi'], function ($session) {
                        return $session['status_kehadiran'] == 4; // Status hadir
                    }));

                    $attendancePercentage = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 2) : 0;

                    $attendances[] = [
                        'kode_mk' => $course['kode_mk'],
                        'nama_mk' => $course['nama'],
                        'sks' => $sksMap[$course['kode_mk']] ?? 0, // Ambil SKS dari Map
                        'total_sessions' => $totalSessions,
                        'attended_sessions' => $attendedSessions,
                        'attendance_percentage' => $attendancePercentage,
                        'presensi' => $course['presensi'],
                    ];
                }

                return view('perkuliahan.absensi', compact('attendances'));
            }

            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data absensi atau nilai.']);
        } catch (\Exception $e) {
            Log::error('Kesalahan API:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan saat mengambil data.']);
        }
    }


    public function showDetail($kodeMk)
    {
        $user = session('user');
        $nim = $user['nim'] ?? null;
        $apiToken = session('api_token');

        if (!$nim) {
            return redirect()->route('login')->withErrors(['error' => 'NIM tidak ditemukan.']);
        }

        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-presensi-by-nim', [
                    'nim' => $nim,
                ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $course = collect($data)->firstWhere('kode_mk', $kodeMk);

                if (!$course) {
                    return redirect()->route('absensi')->withErrors(['error' => 'Data tidak ditemukan.']);
                }

                return view('perkuliahan.detailabsensi', compact('course'));
            }

            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data absensi.']);
        } catch (\Exception $e) {
            Log::error('Kesalahan API:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan saat mengambil data.']);
        }
    }
}
