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
    $apiToken = session('api_token');
    $user = session('user');

    // Allow API call if the user is keasramaan or if a nim exists in session
    if ($apiToken && $user && ($user['role'] === 'keasramaan' || isset($user['nim']))) {
        try {
            // Get behavior score data (get-penilaian API)
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $studentNim,
                ]);
            
            // Handle error if API returns a 500 (no data) by setting an empty array
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

            // Fetch local pelanggaran records from your database for the student.
            $localBehaviors = \App\Models\StudentBehavior::where('student_nim', $studentNim)->get()
                ->groupBy(function ($item) {
                    // Group by TA and semester (e.g., "2022-1")
                    return $item->ta . '-' . $item->semester;
                });

            // Process violation data and merge with local records.
            $pelanggaranList = $pelanggaranData['data'] ?? [];
            foreach ($nilaiPerilaku as &$perilaku) {
                // Convert the numeric semester from API to text, if needed.
                $perilaku['semester'] = $this->convertSemester($perilaku['sem_ta'] ?? 0);
                
                // Filter API pelanggaran data for this TA and semester.
                $filteredPelanggaran = array_filter($pelanggaranList, function ($pelanggaran) use ($perilaku) {
                    return (int) $pelanggaran['ta'] === (int) $perilaku['ta']
                        && (int) $pelanggaran['sem_ta'] === (int) $perilaku['sem_ta'];
                });
                
                // Build a key to match the grouping in local records.
                $semKey = $perilaku['ta'] . '-' . ($perilaku['sem_ta'] ?? 0);
                if (isset($localBehaviors[$semKey])) {
                    // Get only local records of type 'pelanggaran'.
                    $dbPelanggaran = $localBehaviors[$semKey]->where('type', 'pelanggaran')->map(function ($item) {
                        return [
                            'local_id'    => $item->id,        // <-- Add the database primary key here
                            'pelanggaran' => $item->description, // Adjust field if needed
                            'unit'        => $item->unit,
                            'tanggal'     => $item->tanggal,
                            'poin'        => $item->poin,
                            'tindakan'    => $item->tindakan,
                        ];
                    })->toArray();
                    // Merge API and local data.
                    $filteredPelanggaran = array_merge(array_values($filteredPelanggaran), $dbPelanggaran);
                }
                $perilaku['pelanggaran'] = array_values($filteredPelanggaran);
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