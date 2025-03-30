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
        $apiToken = session('api_token');
        $pelanggaranList = collect(); // Inisialisasi list pelanggaran
    
        try {
            $items = Nim::all();
    
            foreach ($items as $item) {
                if (!$item) {
                    continue;
                }
    
                Log::info("Data terambil: Nama - {$item->nama}, NIM - {$item->nim}");
    
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
    
                // Ambil data mahasiswa
                $mahasiswaData = collect($response['data']['mahasiswa'])->map(function ($mhs) {
                    return [
                        'nim' => $mhs['nim'] ?? '-',
                        'nama' => $mhs['nama'] ?? '-',
                        'tahun_masuk' => $mhs['angkatan'] ?? '-',
                        'prodi' => $mhs['prodi_name'] ?? '-',
                    ];
                });
    
                // Ambil data pelanggaran untuk setiap mahasiswa
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
    
                        // Mapping data pelanggaran
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
    
                        // Gabungkan ke dalam list pelanggaran
                        $pelanggaranList = $pelanggaranList->merge($pelanggaranItems);
                    } catch (\Exception $e) {
                        Log::error("Error mengambil data pelanggaran untuk NIM: {$data['nim']}", ['message' => $e->getMessage()]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Exception terjadi saat mengambil data mahasiswa:', ['message' => $e->getMessage()]);
        }
    
        // Pagination dengan 5 data per halaman
        $page = $request->query('page', 1); // Ambil halaman dari query string
        $perPage = 7; // Jumlah data per halaman
        $paginatedPelanggaran = new \Illuminate\Pagination\LengthAwarePaginator(
            $pelanggaranList->forPage($page, $perPage),
            $pelanggaranList->count(),
            $perPage,
            $page,
            ['path' => url()->current()]
        );
    
        return view('konseling.daftar_pelanggaran', ['pelanggaranList' => $paginatedPelanggaran]);
    }
    
}
