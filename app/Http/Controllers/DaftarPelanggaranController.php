<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class DaftarPelanggaranController extends Controller
{
    public function daftarPelanggaran()
    {
        $apiToken = session('api_token');

        // Ambil daftar NIM beserta nama mahasiswa dari database
        $nimData = DB::table('nim')->select('nim', 'nama')->get();
        $pelanggaranList = collect();

        foreach ($nimData as $data) {
            try {
                // Ambil data pelanggaran berdasarkan NIM
                $pelanggaranResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-pelanggaran-mhs", [
                        'nim' => $data->nim,
                        'ta' => '',
                        'sem_ta' => '',
                    ]);

                if ($pelanggaranResponse->successful() && isset($pelanggaranResponse['data'])) {
                    $pelanggaranItems = collect($pelanggaranResponse['data'])->map(function ($item) use ($data) {
                        return [
                            'nim' => $data->nim,
                            'nama' => $data->nama, // Nama langsung dari database
                            'pelanggaran' => $item['pelanggaran'] ?? '-',
                            'tingkat' => $item['tingkat'] ?? '-'
                        ];
                    });

                    $pelanggaranList = $pelanggaranList->merge($pelanggaranItems);
                }
            } catch (\Exception $e) {
                Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
            }
        }



        return view('konseling.daftar_pelanggaran', ['pelanggaranList' => $pelanggaranList]);
    }

}

