<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\StudentBehavior;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OrangTuaController extends Controller
{

    public function index()
    {
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        return view('beranda.homeOrangTua', compact('pengumuman'));
        
    }

    public function catatan_perilaku()
    {
        $user     = session('user');
        $nim      = $user['nim'] ?? null;
        $apiToken = session('api_token');

        if (! $nim || ! $apiToken) {
            return redirect()->route('login')
                ->withErrors(['error' => 'NIM atau API Token tidak ada di session']);
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
                    'nim'    => $nim,
                    'ta'     => '',
                    'sem_ta' => '',
                ]);

            // 3. Ambil data kebaikan dari API
            $kebaikanResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-kebaikan-mhs', [
                    'nim'    => $nim,
                    'ta'     => '',
                    'sem_ta' => '',
                ]);

            // 4. Ambil data catatan perilaku lokal dari DB
            $dbBehaviors = StudentBehavior::where('student_nim', $nim)->get();

            if (
                $response->successful() &&
                $pelanggaranResponse->successful() &&
                $kebaikanResponse->successful()
            ) {
                $data            = $response->json();
                $pelanggaranData = $pelanggaranResponse->json();
                $kebaikanData    = $kebaikanResponse->json();

                // Data API
                $nilaiPerilaku   = array_values($data['Nilai Perilaku'] ?? []);
                $pelanggaranList = $pelanggaranData['data'] ?? [];
                $kebaikanList    = $kebaikanData['data'] ?? [];

                // 5. Kelompokkan data lokal berdasarkan TA-semester
                $behaviorsByTaSem = [];
                foreach ($dbBehaviors as $beh) {
                    $key = $beh->ta . '-' . $beh->semester;
                    if (! isset($behaviorsByTaSem[$key])) {
                        $behaviorsByTaSem[$key] = [
                            'pelanggaran'    => [],
                            'perbuatan_baik' => [],
                        ];
                    }

                    $record = [
                        'poin'    => (int) $beh->poin,
                        'unit'    => $beh->unit,
                        'tanggal' => $beh->tanggal,
                        'tindakan'=> $beh->tindakan,
                    ];

                    if ($beh->type === 'pelanggaran') {
                        $record['pelanggaran'] = $beh->description;
                        $behaviorsByTaSem[$key]['pelanggaran'][] = $record;
                    } elseif ($beh->type === 'perbuatan_baik') {
                        $record['kredit_poin']    = (int) $beh->poin;
                        $record['perbuatan_baik'] = $beh->description;
                        $behaviorsByTaSem[$key]['perbuatan_baik'][] = $record;
                    }
                }

                // 6. Gabungkan API & DB, hitung akumulasi skor
                foreach ($nilaiPerilaku as & $perilaku) {
                    // Konversi semester ke teks
                    $perilaku['semester'] = $this->convertSemester($perilaku['sem_ta'] ?? 0);

                    // Filter per TA & sem
                    $filteredPelanggaranAPI = array_values(array_filter($pelanggaranList, function ($item) use ($perilaku) {
                        return (int) $item['ta']     === (int) $perilaku['ta']
                            && (int) $item['sem_ta'] === (int) $perilaku['sem_ta'];
                    }));
                    $filteredKebaikanAPI = array_values(array_filter($kebaikanList, function ($item) use ($perilaku) {
                        return (int) $item['ta']     === (int) $perilaku['ta']
                            && (int) $item['sem_ta'] === (int) $perilaku['sem_ta'];
                    }));

                    $key             = $perilaku['ta'] . '-' . $perilaku['sem_ta'];
                    $dbPelanggaran   = $behaviorsByTaSem[$key]['pelanggaran']    ?? [];
                    $dbPerbuatanBaik = $behaviorsByTaSem[$key]['perbuatan_baik'] ?? [];

                    $perilaku['pelanggaran']    = array_merge($filteredPelanggaranAPI,   $dbPelanggaran);
                    $perilaku['perbuatan_baik'] = array_merge($filteredKebaikanAPI,      $dbPerbuatanBaik);

                    // Hitung poin
                    $skorAwal              = (int) ($perilaku['akumulasi_skor_awal'] ?? 0);
                    $totalPoinPelanggaran  = 0;
                    foreach ($perilaku['pelanggaran'] as $item) {
                        $totalPoinPelanggaran += (int) ($item['poin'] ?? 0);
                    }
                    $totalKreditPerbuatanBaik = 0;
                    foreach ($perilaku['perbuatan_baik'] as $item) {
                        $totalKreditPerbuatanBaik += (int) ($item['kredit_poin'] ?? ($item['poin'] ?? 0));
                    }

                    $perilaku['akumulasi_skor'] = $skorAwal + $totalPoinPelanggaran - $totalKreditPerbuatanBaik;
                }
                unset($perilaku);

                Log::info('Processed Perilaku', ['nilaiPerilaku' => $nilaiPerilaku]);

                return view('catatanPerilaku.catatan_perilaku_orang_tua', compact('nilaiPerilaku'));
            }

            return redirect()->route('beranda')
                ->withErrors(['error' => 'Gagal mengambil data dari API.']);
        } catch (\Exception $e) {
            Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
            return redirect()->route('orang_tua')
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
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