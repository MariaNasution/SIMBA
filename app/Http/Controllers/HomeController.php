<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index()
    {
        $user = session('user');
        $nim = $user['nim'];
        $apiToken = session('api_token');

        try {

            $studentResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                    'nim' => $nim,
                ]);

            if ($studentResponse->successful()) {
                $studentData = $studentResponse->json()['data'] ?? [];

                // Simpan sem_ta dan ta ke session
                session([
                    'sem_ta' => $studentData['sem_ta'] ?? null,
                    'ta' => $studentData['ta'] ?? null,
                ]);
            }
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false]) // Abaikan verifikasi SSL
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $nim,
                ]);

            Log::info('Respons API mentah:', ['body' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();
                $ipSemester = $data['IP Semester'] ?? [];

                // Urutkan berdasarkan tahun akademik (ta) dan semester (sem)
                uasort($ipSemester, function ($a, $b) {
                    if ($a['ta'] === $b['ta']) {
                        return $a['sem'] <=> $b['sem']; // Urutkan berdasarkan semester jika tahun sama
                    }
                    return $a['ta'] <=> $b['ta']; // Urutkan berdasarkan tahun
                });

                $labels = [];
                $values = [];

                foreach ($ipSemester as $semester => $details) {
                    // Tambahkan label dan nilai dengan placeholder jika ip_semester tidak valid
                    $labels[] = "TA {$details['ta']} - Semester {$details['sem']}";
                    $values[] = is_numeric($details['ip_semester']) ? (float) $details['ip_semester'] : 0;
                }

                Log::info('Data labels:', $labels);
                Log::info('Data values:', $values);

                $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
                $akademik = Calendar::where('type', 'akademik')->latest()->first();
                $bem = Calendar::where('type', 'bem')->latest()->first();


                return view('beranda.home', compact('labels', 'values', 'pengumuman', 'akademik', 'bem'));
            }

            Log::error('Gagal mengambil data API', ['response' => $response->body()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data kemajuan studi.']);
        } catch (\Exception $e) {
            Log::error('Kesalahan API:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan saat memuat data.']);
        }
    }

    public function show($id)
    {
        // Ambil pengumuman berdasarkan ID
        $pengumuman = Pengumuman::findOrFail($id);

        // Kembalikan ke view dengan pengumuman yang ditemukan
        return view('beranda.detailpengumuman', compact('pengumuman'));
    }
}
