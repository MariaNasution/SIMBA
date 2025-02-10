<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KemajuanStudiController extends Controller
{
    public function index()
    {
        $user = session('user');
        $nim = $user['nim'];
        $apiToken = session('api_token');

        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(60)
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $nim,
                ]);

            $response2 = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(60)
                ->get('https://cis-dev.del.ac.id/api/library-api/nilai-akhir', [
                    'nim' => $nim,
                ]);

            if ($response->successful() && $response2->successful()) {
                $data = $response->json();
                $data2 = $response2->json();
                $ipSemester = $data['IP Semester'] ?? [];

                // Urutkan data berdasarkan tahun akademik (ta) dan semester (sem_ta)
                uasort($ipSemester, function ($a, $b) {
                    if ($a['ta'] === $b['ta']) {
                        return $a['sem_ta'] <=> $b['sem_ta'];
                    }
                    return $a['ta'] <=> $b['ta'];
                });

                // Simpan nilai semester (sem) ke session
                session(['sem' => collect($ipSemester)->pluck('sem')->unique()->toArray()]);

                // Siapkan labels dan values untuk chart
                $labels = [];
                $values = [];

                foreach ($ipSemester as $details) {
                    $labels[] = "TA {$details['ta']} - Semester {$details['sem']}";
                    $values[] = is_numeric($details['ip_semester']) ? (float) $details['ip_semester'] : 0;
                }

                // Memproses mata kuliah per semester
                $matkulPerSemester = [];
                $semesterOrder = []; // Menyimpan urutan semester dari mata kuliah

                foreach ($data2['data'] as $matkul) {
                    $semesterKey = "TA {$matkul['ta']} - Semester {$matkul['sem_ta']}";

                    if (!isset($matkulPerSemester[$semesterKey])) {
                        $matkulPerSemester[$semesterKey] = [];
                    }
                    $matkulPerSemester[$semesterKey][] = $matkul;

                    if (!in_array($semesterKey, $semesterOrder)) {
                        $semesterOrder[] = $semesterKey;
                    }
                }

                // Mengurutkan semester berdasarkan urutan yang ditemukan pada data mata kuliah
                $sortedSemesterData = [];
                foreach ($semesterOrder as $semesterKey) {
                    $semesterData = $matkulPerSemester[$semesterKey];

                    // Ambil nilai IP semester dari $ipSemester berdasarkan TA dan sem_ta
                    $matchingIpSemester = array_filter($ipSemester, function ($item) use ($semesterData) {
                        return $item['ta'] == $semesterData[0]['ta'] && $item['sem_ta'] == $semesterData[0]['sem_ta'];
                    });

                    $matchingIpSemester = reset($matchingIpSemester); // Ambil elemen pertama yang cocok

                    $sortedSemesterData[] = [
                        'semester' => $semesterKey,
                        'ta' => $semesterData[0]['ta'],
                        'sem' => $semesterData[0]['sem_ta'],
                        'ip_semester' => $matchingIpSemester['ip_semester'] ?? 'Belum di-generate',
                    ];
                }

                return view('perkuliahan.kemajuan_studi', compact('labels', 'values', 'data', 'data2', 'matkulPerSemester', 'sortedSemesterData'));
            }

            Log::error('Gagal mengambil data kemajuan studi:', ['response' => $response->body()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data kemajuan studi.']);
        } catch (\Exception $e) {
            Log::error('Kesalahan API kemajuan studi:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan saat memuat data kemajuan studi.']);
        }
    }
}
