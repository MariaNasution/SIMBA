<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\StudentBehavior; // Import the StudentBehavior model

class CatatanPerilakuController extends Controller
{
    public function index()
{
    $user = session('user');
    $nim = $user['nim'] ?? null;
    $apiToken = session('api_token');

    if (!$nim || !$apiToken) {
        return redirect()->route('login')->withErrors(['error' => 'NIM atau API Token tidak ada di session']);
    }

    try {
        // 1. Ambil data nilai perilaku dari API
        $response = Http::withToken($apiToken)
            ->withOptions(['verify' => false])
            ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                'nim' => $nim,
            ]);

        // 2. Ambil data pelanggaran dari API
        $pelanggaranResponse = Http::withToken($apiToken)
            ->withOptions(['verify' => false])
            ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-pelanggaran-mhs', [
                'nim' => $nim,
                'ta'  => '',
                'sem_ta' => '',
            ]);

        // 3. Ambil data catatan perilaku lokal dari DB
        $dbBehaviors = StudentBehavior::where('student_nim', $nim)->get();

        if ($response->successful() && $pelanggaranResponse->successful()) {
            $data = $response->json();
            $pelanggaranData = $pelanggaranResponse->json();

            // 4. Data nilai perilaku dari API
            $nilaiPerilaku = $data['Nilai Perilaku'] ?? [];
            $nilaiPerilaku = array_values($nilaiPerilaku);

            // 5. Data pelanggaran dari API
            $pelanggaranList = $pelanggaranData['data'] ?? [];
            Log::info('Pelanggaran List API', ['list' => $pelanggaranList]);

            // 6. Kelompokkan data lokal berdasarkan kombinasi TA dan semester
            $behaviorsByTaSem = [];  // key = "$ta-$sem_ta"
            foreach ($dbBehaviors as $beh) {
                $key = $beh->ta . '-' . $beh->semester;
                if (!isset($behaviorsByTaSem[$key])) {
                    $behaviorsByTaSem[$key] = [
                        'pelanggaran' => [],
                        'perbuatan_baik' => [],
                    ];
                }
                // Bentuk data, sesuaikan dengan struktur yang kita inginkan
                $record = [
                    'poin' => (int) $beh->poin,
                    'unit' => $beh->unit,
                    'tanggal' => $beh->tanggal,
                    'tindakan' => $beh->tindakan,
                    // Catatan: description berisi informasi sesuai tipe
                ];

                if ($beh->type === 'pelanggaran') {
                    // Untuk pelanggaran, gunakan 'poin'
                    $record['pelanggaran'] = $beh->description;
                    $behaviorsByTaSem[$key]['pelanggaran'][] = $record;
                } elseif ($beh->type === 'perbuatan_baik') {
                    // Untuk perbuatan baik, kita gunakan 'kredit_poin'
                    $record['kredit_poin'] = (int) $beh->poin; // atau bisa juga menggunakan field 'kredit_poin' jika ada
                    $record['perbuatan_baik'] = $beh->description;
                    $behaviorsByTaSem[$key]['perbuatan_baik'][] = $record;
                }
            }

            // 7. Gabungkan data API dengan data DB dan hitung akumulasi skor
            foreach ($nilaiPerilaku as &$perilaku) {
                // Konversi sem_ta menjadi teks (jika diperlukan)
                $perilaku['semester'] = $this->convertSemester($perilaku['sem_ta'] ?? 0);

                // Filter data pelanggaran API untuk TA dan sem_ta tertentu
                $filteredPelanggaranAPI = array_filter($pelanggaranList, function ($item) use ($perilaku) {
                    return (int) $item['ta'] === (int) $perilaku['ta']
                        && (int) $item['sem_ta'] === (int) $perilaku['sem_ta'];
                });
                $filteredPelanggaranAPI = array_values($filteredPelanggaranAPI);

                // Gunakan key TA-semester untuk data DB
                $key = $perilaku['ta'] . '-' . $perilaku['sem_ta'];
                $dbPelanggaran = $behaviorsByTaSem[$key]['pelanggaran'] ?? [];
                $dbPerbuatanBaik = $behaviorsByTaSem[$key]['perbuatan_baik'] ?? [];

                // Gabungkan API dan DB untuk pelanggaran
                $perilaku['pelanggaran'] = array_merge($filteredPelanggaranAPI, $dbPelanggaran);
                // Ambil perbuatan baik dari DB (atau juga merge jika API juga mengirim data)
                $perilaku['perbuatan_baik'] = $dbPerbuatanBaik;

                // 8. Hitung akumulasi skor:
                // Misal, formula: Akumulasi Skor = Skor Awal – Total Poin Pelanggaran + Total Kredit Poin Perbuatan Baik
                $skorAwal = isset($perilaku['akumulasi_skor_awal']) ? (int) $perilaku['akumulasi_skor_awal'] : 0;
                $totalPoinPelanggaran = 0;
                $totalKreditPerbuatanBaik = 0;

                // Sum poin dari API dan DB untuk pelanggaran
                foreach ($perilaku['pelanggaran'] as $item) {
                    // Asumsikan field 'poin' ada di API dan DB
                    if (isset($item['poin'])) {
                        $totalPoinPelanggaran += (int) $item['poin'];
                    }
                }

                // Sum kredit poin perbuatan baik dari DB (atau API jika ada)
                foreach ($perilaku['perbuatan_baik'] as $item) {
                    if (isset($item['kredit_poin'])) {
                        $totalKreditPerbuatanBaik += (int) $item['kredit_poin'];
                    }
                }

                // Hitung akumulasi
                $perilaku['akumulasi_skor'] = $skorAwal + $totalPoinPelanggaran - $totalKreditPerbuatanBaik;
            }
            Log::info('Processed Perilaku with Accumulated Score', ['nilaiPerilaku' => $nilaiPerilaku]);

            return view('catatanPerilaku.catatan_perilaku_mahasiswa', compact('nilaiPerilaku'));
        }
        return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data dari API.']);
    } catch (\Exception $e) {
        Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
        return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
}

private function convertSemester($sem_ta)
{
    switch ($sem_ta) {
        case 1:
            return 'Gasal';
        case 2:
            return 'Genap';
        case 3:
            return 'Pendek';
        default:
            return 'Tidak Diketahui';
    }
}
}
