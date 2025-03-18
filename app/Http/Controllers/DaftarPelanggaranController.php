<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Nim;

class DaftarPelanggaranController extends Controller
{
    public function daftarPelanggaran()
    {
        $apiToken = session('api_token');
        $pelanggaranList = collect(); // Inisialisasi list pelanggaran

        try {
            $items = Nim::all();

            foreach ($items as $item) {
                // Pastikan data tidak bernilai false
                if (!$item) {
                    continue;
                }
                Log::info(("Data terambil adalah" . $item->nama . $item->nim));
                // Ambil data mahasiswa dari API
                $response = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("https://cis-dev.del.ac.id/api/library-api/mahasiswa", [
                        'nim' => $item->nim ?? '-',
                        'nama' => $item->nama ?? '-',
                    ]);

                // Pastikan response sukses dan memiliki data
                if (!$response->successful() || empty($response['data']['mahasiswa'])) {
                    Log::error('Gagal mengambil data mahasiswa untuk NIM: ' . $item->nim);
                    continue;
                }

                // Ambil hanya NIM dan Nama mahasiswa
                $mahasiswaData = collect($response['data']['mahasiswa'])->map(function ($mhs) {
                    return [
                        'nim' => $mhs['nim'] ?? '-',
                        'nama' => $mhs['nama'] ?? '-',
                    ];
                });

                // Looping setiap mahasiswa untuk mengambil data pelanggaran berdasarkan NIM
                foreach ($mahasiswaData as $data) {
                    try {
                        $pelanggaranResponse = Http::withToken($apiToken)
                            ->withOptions(['verify' => false])
                            ->get("https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-pelanggaran-mhs", [
                                'nim' => $data['nim'],
                                'ta' => '',
                                'sem_ta' => '',
                            ]);


                        // Cek apakah respons API kedua berhasil
                        if (!$pelanggaranResponse->successful() || empty($pelanggaranResponse['data'])) {
                            Log::error("Gagal mengambil data pelanggaran untuk NIM: {$data['nim']}");
                            continue;
                        }

                        // Mapping data pelanggaran
                        $pelanggaranItems = collect($pelanggaranResponse['data'])->map(function ($item) use ($data) {
                            return [
                                'nim' => $data['nim'],
                                'nama' => $data['nama'], // Ambil nama dari API pertama
                                'pelanggaran' => $item['pelanggaran'] ?? '-',
                                'tingkat' => $item['tingkat'] ?? '-'
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

        return view('konseling.daftar_pelanggaran', ['pelanggaranList' => $pelanggaranList]);
    }
}
