<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        // Contoh dummy data untuk nilai perilaku
    $dummyNilaiPerilaku = [
        [
            'ta'          => '2024/2025',
            'sem_ta'      => 1,
            // Nilai ini nantinya akan dikonversi menjadi 'Gasal' melalui method convertSemester()
            'pelanggaran' => [
                [
                    'pelanggaran' => 'Terlambat masuk asrama',
                    'unit'        => 'Keasramaan',
                    'tanggal'     => '2024-02-10',
                    'poin'        => 5,
                    'tindakan'    => 'Peringatan lisan',
                    'id'    => 1,
                ],
                [
                    'pelanggaran' => 'Tidak mengikuti peraturan asrama',
                    'unit'        => 'Keasramaan',
                    'tanggal'     => '2024-02-15',
                    'poin'        => 3,
                    'tindakan'    => 'Surat peringatan',
                    'id'    => 2,
                ],
            ],
            'perbuatan_baik' => [
                [
                    'perbuatan_baik' => 'Membantu kegiatan kebersihan asrama',
                    'unit'           => 'Keasramaan',
                    'tanggal'        => '2024-03-15',
                    'poin'           => 10,
                    'tindakan'       => 'Penghargaan',
                    'id'       => 3,
                ],
            ],
        ],
        [
            'ta'          => '2024/2025',
            'sem_ta'      => 2,
            'pelanggaran' => [],
            'perbuatan_baik' => [
                [
                    'perbuatan_baik' => 'Aktif mengikuti kegiatan keasramaan',
                    'unit'           => 'Keasramaan',
                    'tanggal'        => '2024-09-05',
                    'poin'           => 8,
                    'tindakan'       => 'Apresiasi',
                    'id'       => 4,
                ],
            ],
        ],
    ];

    // Jika kamu ingin menggunakan dummy data, comment atau bypass API calls di bawah ini.
    // Kembalikan dummy data ke view.
    return view('catatanPerilaku.catatan_perilaku_detail', [
        'nilaiPerilaku' => $dummyNilaiPerilaku,
        'studentNim'    => $studentNim,
    ]);

    /*
        $apiToken = session('api_token');
        $user = session('user');

        if ($apiToken && $user && ($user['role'] === 'keasramaan' || isset($user['nim']))) {
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

                // Fetch local behavior records for the student and group by "TA-semester"
                $localBehaviors = \App\Models\StudentBehavior::where('student_nim', $studentNim)->get()
                    ->groupBy(function ($item) {
                        return $item->ta . '-' . $item->semester;
                    });

                // Process API pelanggaran data and merge with local records
                $pelanggaranList = $pelanggaranData['data'] ?? [];

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

                    // Merge local pelanggaran records
                    if (isset($localBehaviors[$semKey])) {
                        $dbPelanggaran = $localBehaviors[$semKey]
                            ->where('type', 'pelanggaran')
                            ->map(function ($item) {
                                return [
                                    'local_id'    => $item->id,
                                    'pelanggaran' => $item->description, // stored value
                                    'unit'        => $item->unit,
                                    'tanggal'     => $item->tanggal,
                                    'poin'        => $item->poin,
                                    'tindakan'    => $item->tindakan,
                                ];
                            })->toArray();
                        $filteredPelanggaran = array_merge(array_values($filteredPelanggaran), $dbPelanggaran);
                    }
                    $perilaku['pelanggaran'] = array_values($filteredPelanggaran);

                    // Merge local perbuatan_baik records (no API assumed for perbuatan_baik)
                    if (isset($localBehaviors[$semKey])) {
                        $dbPerbuatanBaik = $localBehaviors[$semKey]
                            ->where('type', 'perbuatan_baik')
                            ->map(function ($item) {
                                return [
                                    'local_id'       => $item->id,
                                    'perbuatan_baik' => $item->description, // stored value
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
                }

                return view('catatanPerilaku.catatan_perilaku_detail', compact('nilaiPerilaku', 'studentNim'));
            } catch (\Exception $e) {
                Log::error('Exception occurred in detail:', ['message' => $e->getMessage()]);
                return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
        } else {
            return redirect()->back()->withErrors(['error' => 'Session data tidak lengkap.']);
        }
            */
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