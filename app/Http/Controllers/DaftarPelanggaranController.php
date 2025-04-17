<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Nim;

class DaftarPelanggaranController extends Controller
{
    public function daftarPelanggaran(Request $request)
    {
        // Validasi input pencarian
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $apiToken = session('api_token');
        $pelanggaranList = collect();
        $search = $request->query('search');

        try {
            $items = Nim::all();

            foreach ($items as $item) {
                if (!$item) {
                    continue;
                }

                // Perbaiki log untuk hanya menggunakan nim
                Log::info("Mengambil data untuk NIM: {$item->nim}");

                // Ambil data mahasiswa dari API
                $response = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("https://cis-dev.del.ac.id/api/library-api/mahasiswa", [
                        'nim' => $item->nim,
                    ]);

                if (!$response->successful() || empty($response['data']['mahasiswa'])) {
                    Log::error("Gagal mengambil data mahasiswa untuk NIM: {$item->nim}");
                    continue;
                }

                $mahasiswaData = collect($response['data']['mahasiswa'])->map(function ($mhs) {
                    return [
                        'nim' => $mhs['nim'] ?? '-',
                        'nama' => $mhs['nama'] ?? '-',
                        'tahun_masuk' => $mhs['angkatan'] ?? '-',
                        'prodi' => $mhs['prodi_name'] ?? '-',
                    ];
                });

                foreach ($mahasiswaData as $data) {
                    try {
                        $pelanggaranResponse = Http::withToken($apiToken)
                            ->withOptions(['verify' => false])
                            ->get("https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-pelanggaran-mhs", [
                                'nim' => $data['nim'],
                                'ta' => '',
                                'sem_ta' => '',
                            ]);

                        if (!$pelanggaranResponse->successful() || empty($pelanggaranResponse['data'])) {
                            Log::error("Gagal mengambil data pelanggaran untuk NIM: {$data['nim']}");
                            continue;
                        }

                        $pelanggaranItems = collect($pelanggaranResponse['data'])->map(function ($item) use ($data) {
                            return [
                                'nim' => $data['nim'],
                                'nama' => $data['nama'],
                                'tahun_masuk' => $data['tahun_masuk'],
                                'prodi' => $data['prodi'],
                                'pelanggaran' => $item['pelanggaran'] ?? '-',
                                'tingkat' => $item['tingkat'] ?? '-',
                            ];
                        });

                        $pelanggaranList = $pelanggaranList->merge($pelanggaranItems);
                    } catch (\Exception $e) {
                        Log::error("Error mengambil data pelanggaran untuk NIM: {$data['nim']}", ['message' => $e->getMessage()]);
                    }
                }
            }

            // Filter berdasarkan NIM atau Nama
            if (!empty($search)) {
                $pelanggaranList = $pelanggaranList->filter(function ($item) use ($search) {
                    return stripos($item['nim'], $search) !== false || stripos($item['nama'], $search) !== false;
                });
            }

        } catch (\Exception $e) {
            Log::error('Exception terjadi saat mengambil data mahasiswa:', ['message' => $e->getMessage()]);
        }

        // Pagination dengan mempertahankan parameter search
        $page = $request->query('page', 1);
        $perPage = 7;
        $paginatedPelanggaran = new \Illuminate\Pagination\LengthAwarePaginator(
            $pelanggaranList->forPage($page, $perPage),
            $pelanggaranList->count(),
            $perPage,
            $page,
            [
                'path' => url()->current(),
                'query' => $request->query->all(), // Pertahankan semua parameter query, termasuk search
            ]
        );

        return view('konseling.daftar_pelanggaran', [
            'pelanggaranList' => $paginatedPelanggaran,
            'error' => $pelanggaranList->isEmpty() && $items->isNotEmpty() ? 'Tidak ada data pelanggaran yang ditemukan.' : null,
        ]);
    }
}