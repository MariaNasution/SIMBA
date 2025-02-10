<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DetailNilaiController extends Controller
{
    public function show($kode_mk)
    {
        $user = session('user');
        $nim = $user['nim'];
        $apiToken = session('api_token');

        // Ambil data nilai akhir
        $nilaiAkhirResponse = Http::withToken($apiToken)
            ->withOptions(['verify' => false])
            ->get('https://cis-dev.del.ac.id/api/library-api/nilai-akhir', [
                'nim' => $nim,
            ]);

        // Ambil data detail nilai
        $detailNilaiResponse = Http::withToken($apiToken)
            ->withOptions(['verify' => false])
            ->timeout(120)
            ->get('https://cis-dev.del.ac.id/api/library-api/detail-nilai-by-nim', [
                'nim' => $nim,
            ]);

        if ($nilaiAkhirResponse->successful() && $detailNilaiResponse->successful()) {
            $nilaiAkhirData = $nilaiAkhirResponse->json()['data'] ?? [];
            $detailNilaiData = $detailNilaiResponse->json()['data'] ?? [];

            // Filter data berdasarkan kode mata kuliah
            $matkul = collect($nilaiAkhirData)->firstWhere('kode_mk', $kode_mk);
            $detailNilai = collect($detailNilaiData)->where('kode_mk', $kode_mk)->first();

            if (!$matkul) {
                return redirect()->route('kemajuan_studi')->with('error', 'Mata Kuliah tidak ditemukan.');
            }

            $ta = $matkul['ta'];
            $sem_ta = $matkul['sem_ta'];

            // Ambil data penilaian berdasarkan TA dan sem_ta
            $penilaianResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(60)
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $nim,
                    'ta' => $ta,
                    'sem_ta' => $sem_ta,
                ]);

            $penilaian = null;

            if ($penilaianResponse->successful()) {
                $ipSemester = $penilaianResponse->json()['IP Semester'] ?? [];

                // Cari penilaian yang sesuai dengan TA dan sem_ta
                foreach ($ipSemester as $key => $value) {
                    if ($value['ta'] == $ta && $value['sem_ta'] == $sem_ta) {
                        $penilaian = $value;
                        break;
                    }
                }
            }

            return view('perkuliahan.detailnilai', compact('matkul', 'detailNilai', 'penilaian'));
        }

        return redirect()->route('kemajuan_studi')->with('error', 'Gagal mengambil data dari API.');
    }
}
