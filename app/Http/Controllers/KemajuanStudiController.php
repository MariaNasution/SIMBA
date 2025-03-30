<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KemajuanStudiController extends Controller
{
    public function index()
    {
        $user = session('user');
        $nim = $user['nim'] ?? null;
        $apiToken = session('api_token');

        // Default/fallback values in case the API fails
        $data = [];
        $data2 = ['data' => []];
        $ipSemester = [];
        $labels = [];
        $values = [];
        $matkulPerSemester = [];
        $sortedSemesterData = [];

        if ($nim && $apiToken) {
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

                    // Sort IP Semester by academic year (ta) and semester (sem_ta)
                    uasort($ipSemester, function ($a, $b) {
                        if ($a['ta'] === $b['ta']) {
                            return $a['sem_ta'] <=> $b['sem_ta'];
                        }
                        return $a['ta'] <=> $b['ta'];
                    });

                    // Store unique semester values to session
                    session(['sem' => collect($ipSemester)->pluck('sem')->unique()->toArray()]);

                    // Prepare labels and values for chart
                    foreach ($ipSemester as $details) {
                        $labels[] = "TA {$details['ta']} - Semester {$details['sem']}";
                        $values[] = is_numeric($details['ip_semester']) ? (float) $details['ip_semester'] : 0;
                    }

                    // Process courses per semester
                    $matkulPerSemester = [];
                    $semesterOrder = [];

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

                    // Sort semester data based on the order found in courses data
                    foreach ($semesterOrder as $semesterKey) {
                        $semesterData = $matkulPerSemester[$semesterKey];
                        // Retrieve matching IP semester entry
                        $matchingIpSemester = array_filter($ipSemester, function ($item) use ($semesterData) {
                            return $item['ta'] == $semesterData[0]['ta'] && $item['sem_ta'] == $semesterData[0]['sem_ta'];
                        });
                        $matchingIpSemester = reset($matchingIpSemester);

                        $sortedSemesterData[] = [
                            'semester'    => $semesterKey,
                            'ta'          => $semesterData[0]['ta'],
                            'sem'         => $semesterData[0]['sem_ta'],
                            'ip_semester' => $matchingIpSemester['ip_semester'] ?? 'Belum di-generate',
                        ];
                    }
                } else {
                    Log::error('Gagal mengambil data kemajuan studi:', [
                        'penilaian_status' => $response->status(),
                        'nilai_akhir_status' => $response2->status()
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Kesalahan API kemajuan studi:', ['message' => $e->getMessage()]);
            }
        } else {
            Log::error('Missing NIM or API token in session.');
        }

        return view('perkuliahan.kemajuan_studi', compact('labels', 'values', 'data', 'data2', 'matkulPerSemester', 'sortedSemesterData'));
    }
}
