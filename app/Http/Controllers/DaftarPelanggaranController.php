<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DaftarPelanggaranController extends Controller
{
    public function daftarPelanggaran()
    {
        $apiToken = session('api_token');

        try {
            // Ambil data mahasiswa dari API pertama
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get("https://cis-dev.del.ac.id/api/library-api/mahasiswa");

            // Pastikan response sukses
            if (!$response->successful() || !isset($response['data']['mahasiswa'])) {
                Log::error('Gagal mengambil data mahasiswa.');
                return view('konseling.daftar_pelanggaran', ['pelanggaranList' => collect()]);
            }

            // Ambil hanya NIM dan Nama mahasiswa
            $mahasiswaData = collect($response['data']['mahasiswa'])->take(4)->map(function ($item) {
                return [
                    'nim' => $item['nim'] ?? '-',
                    'nama' => $item['nama'] ?? '-',
                ];
            });

            $pelanggaranList = collect();

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

                    // Debugging: Cek apakah respons API kedua berhasil
                    if (!$pelanggaranResponse->successful() || !isset($pelanggaranResponse['data'])) {
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

        } catch (\Exception $e) {
            Log::error('Exception terjadi saat mengambil data mahasiswa:', ['message' => $e->getMessage()]);
            $pelanggaranList = collect(); // Kosongkan jika gagal mengambil data
        }

        return view('konseling.daftar_pelanggaran', ['pelanggaranList' => $pelanggaranList]);
    }
}
