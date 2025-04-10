<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\StudentBehavior;

class KeasramaanController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        return view('beranda.homeKeasramaan', compact('pengumuman'));
    }

    public function pelanggaran()
    {
        return view('catatanPerilaku.catatan_perilaku_keasramaan');
    }

    public function detail($studentNim)
    {
        $apiToken = session('api_token');
        $user = session('user');

        if (!$apiToken || !$user || !($user['role'] === 'keasramaan' || isset($user['nim']))) {
            return redirect()->back()->withErrors(['error' => 'Session data tidak lengkap.']);
        }

        try {
            // Get behavior score data from API
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $studentNim,
                ]);

            if (!$response->successful()) {
                if ($response->status() == 500) {
                    $nilaiPerilaku = [];
                } else {
                    return redirect()->back()->withErrors(['error' => 'Gagal mengambil data penilaian dari API.']);
                }
            } else {
                $data = $response->json();
                $nilaiPerilaku = $data['Nilai Perilaku'] ?? [];
                $nilaiPerilaku = array_values($nilaiPerilaku);
            }

            // Get violation data (pelanggaran API)
            $pelanggaranResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-pelanggaran-mhs', [
                    'nim'    => $studentNim,
                    'ta'     => '',
                    'sem_ta' => '',
                ]);

            if (!$pelanggaranResponse->successful()) {
                if ($pelanggaranResponse->status() == 500) {
                    $pelanggaranData = ['data' => []];
                } else {
                    return redirect()->back()->withErrors(['error' => 'Gagal mengambil data pelanggaran dari API.']);
                }
            } else {
                $pelanggaranData = $pelanggaranResponse->json();
            }

            // Preprocess API pelanggaran data: normalize each record so it always has an 'id' key.
            $pelanggaranList = array_map(function ($record) {
                if (is_array($record)) {
                    return array_merge(['id' => null], $record);
                }
                return ['id' => null];
            }, $pelanggaranData['data'] ?? []);

            // Fetch local behavior records for the student and group by "TA-semester"
            $localBehaviors = StudentBehavior::where('student_nim', $studentNim)->get()
                ->groupBy(function ($item) {
                    return $item->ta . '-' . $item->semester;
                });

            // Process API pelanggaran data and merge with local records
            foreach ($nilaiPerilaku as &$perilaku) {
                // Convert numeric semester to text if needed.
                $perilaku['semester'] = $this->convertSemester($perilaku['sem_ta'] ?? 0);

                // Filter API pelanggaran data for this TA and semester.
                $filteredPelanggaran = array_filter($pelanggaranList, function ($pelanggaran) use ($perilaku) {
                    return (int)$pelanggaran['ta'] === (int)$perilaku['ta']
                        && (int)$pelanggaran['sem_ta'] === (int)$perilaku['sem_ta'];
                });

                // Build a key to match local records grouping.
                $semKey = $perilaku['ta'] . '-' . ($perilaku['sem_ta'] ?? 0);

                // Merge local pelanggaran records if available
                if (isset($localBehaviors[$semKey])) {
                    $dbPelanggaran = $localBehaviors[$semKey]
                        ->where('type', 'pelanggaran')
                        ->map(function ($item) {
                            return [
                                // Use the local record's actual ID
                                'id'           => $item->id,
                                'pelanggaran'  => $item->description,
                                'unit'         => $item->unit,
                                'tanggal'      => $item->tanggal,
                                'poin'         => $item->poin,
                                'tindakan'     => $item->tindakan,
                            ];
                        })->toArray();
                    // Merge normalized API items with local records.
                    $filteredPelanggaran = array_merge(array_values($filteredPelanggaran), $dbPelanggaran);
                }
                $perilaku['pelanggaran'] = array_values($filteredPelanggaran);

                // Merge local perbuatan_baik records (API does not provide these)
                if (isset($localBehaviors[$semKey])) {
                    $dbPerbuatanBaik = $localBehaviors[$semKey]
                        ->where('type', 'perbuatan_baik')
                        ->map(function ($item) {
                            return [
                                'id'             => $item->id,
                                'perbuatan_baik' => $item->description,
                                'unit'           => $item->unit,
                                'tanggal'        => $item->tanggal,
                                'poin'           => $item->poin,
                                'tindakan'       => $item->tindakan,
                            ];
                        })->toArray();
                    $perilaku['perbuatan_baik'] = array_values($dbPerbuatanBaik);
                } else {
                    $perilaku['perbuatan_baik'] = [];
                }

                // === Hitung Akumulasi Skor ===
                // Misal, skor awal default adalah 0
                $skorAwal = 0;

                // Total poin pelanggaran
                $totalPoinPelanggaran = 0;
                if (!empty($perilaku['pelanggaran'])) {
                    foreach ($perilaku['pelanggaran'] as $pelanggaran) {
                        $totalPoinPelanggaran += isset($pelanggaran['poin']) ? (int)$pelanggaran['poin'] : 0;
                    }
                }

                // Total kredit perbuatan baik
                $totalKreditPerbuatanBaik = 0;
                if (!empty($perilaku['perbuatan_baik'])) {
                    foreach ($perilaku['perbuatan_baik'] as $baik) {
                        $totalKreditPerbuatanBaik += isset($baik['poin']) ? (int)$baik['poin'] : 0;
                    }
                }

                // Hitung akumulasi skor menggunakan formula: skor awal + total poin pelanggaran - total kredit perbuatan baik.
                $perilaku['akumulasi_skor'] = $skorAwal + $totalPoinPelanggaran - $totalKreditPerbuatanBaik;
            }

            return view('catatanPerilaku.catatan_perilaku_detail', [
                'nilaiPerilaku' => $nilaiPerilaku,
                'studentNim'    => $studentNim,
            ]);
        } catch (\Exception $e) {
            Log::error('Exception occurred in detail:', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
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
