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
        // Hapus dummy data, sehingga API call dan pengolahan data dijalankan

        $apiToken = session('api_token');
        $user = session('user');

        if ($apiToken && $user && ($user['role'] === 'keasramaan' || isset($user['nim']))) {
            try {
                // Ambil data nilai perilaku dari API
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

                // Ambil data pelanggaran dari API
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

                // Ambil data perilaku lokal dari database dan group berdasarkan "TA-semester"
                $localBehaviors = \App\Models\StudentBehavior::where('student_nim', $studentNim)->get()
                    ->groupBy(function ($item) {
                        return $item->ta . '-' . $item->semester;
                    });

                // Proses data pelanggaran dari API dan gabungkan dengan data lokal
                $pelanggaranList = $pelanggaranData['data'] ?? [];

                foreach ($nilaiPerilaku as &$perilaku) {
                    // Konversi semester numerik ke teks (misalnya 'Gasal', 'Genap', dll)
                    $perilaku['semester'] = $this->convertSemester($perilaku['sem_ta'] ?? 0);

                    // Filter data pelanggaran API untuk TA dan semester yang sesuai
                    $filteredPelanggaran = array_filter($pelanggaranList, function ($pelanggaran) use ($perilaku) {
                        return (int)$pelanggaran['ta'] === (int)$perilaku['ta']
                            && (int)$pelanggaran['sem_ta'] === (int)$perilaku['sem_ta'];
                    });

                    // Buat key untuk mencocokkan data lokal
                    $semKey = $perilaku['ta'] . '-' . ($perilaku['sem_ta'] ?? 0);

                    // Gabungkan data pelanggaran dari database lokal
                    if (isset($localBehaviors[$semKey])) {
                        $dbPelanggaran = $localBehaviors[$semKey]
                            ->where('type', 'pelanggaran')
                            ->map(function ($item) {
                                return [
                                    'id'    => $item->id,
                                    'pelanggaran' => $item->description,
                                    'unit'        => $item->unit,
                                    'tanggal'     => $item->tanggal,
                                    'poin'        => $item->poin,
                                    'tindakan'    => $item->tindakan,
                                ];
                            })->toArray();
                        $filteredPelanggaran = array_merge(array_values($filteredPelanggaran), $dbPelanggaran);
                    }
                    $perilaku['pelanggaran'] = array_values($filteredPelanggaran);

                    // Gabungkan data perbuatan baik dari database lokal (tidak ada API untuk ini)
                    if (isset($localBehaviors[$semKey])) {
                        $dbPerbuatanBaik = $localBehaviors[$semKey]
                            ->where('type', 'perbuatan_baik')
                            ->map(function ($item) {
                                return [
                                    'id'       => $item->id,
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
                }

                return view('catatanPerilaku.catatan_perilaku_detail', compact('nilaiPerilaku', 'studentNim'));
            } catch (\Exception $e) {
                Log::error('Exception occurred in detail:', ['message' => $e->getMessage()]);
                return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
        } else {
            return redirect()->back()->withErrors(['error' => 'Session data tidak lengkap.']);
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