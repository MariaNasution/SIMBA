<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Tambahkan ini
use Illuminate\Support\Facades\Log;

class RiwayatKonselingController extends Controller
{
           public function index()
        {
            $user = session('user');
            $apiToken = session('api_token');
    
            // Jika tidak ada token, kembalikan dengan error
            if (!$apiToken) {
                return redirect()->back()->withErrors(['error' => 'API token tidak tersedia']);
            }
            try {
                // Ambil semua data mahasiswa dari API tanpa filter apapun
                $mahasiswaResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get('https://cis-dev.del.ac.id/api/library-api/mahasiswa');   
                    

                if ($mahasiswaResponse->successful()) {

                    // Ambil data dari response API
                    $mahasiswas = $mahasiswaResponse->json()['data']['mahasiswa'];
                    // dd($mahasiswas);
                    return view('konseling.riwayat_konseling', compact('mahasiswas'));
                }
    
                return redirect()->back()->withErrors(['error' => 'Gagal mengambil data mahasiswa dari API.']);
            } catch (\Exception $e) {
                Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
                return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
        }
        public function CariRiwayatMahasiswa(Request $request)
    {
        $nim = $request->input('nim');
        $apiToken = session('api_token');
        if (!$apiToken) {
            return redirect()->back()->withErrors(['error' => 'API token tidak tersedia']);

        }
        
        try {

            // Ambil data mahasiswa berdasarkan NIM
            $mahasiswaResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                    'nim' => $nim,
                ]);
            
            if ($mahasiswaResponse->successful()) {
                // Ambil data dari response API
                $mahasiswa = $mahasiswaResponse->json();
                $dataMahasiswa = [
                    'nim' => $mahasiswa['data']['nim'] ?? '',
                    'nama' => $mahasiswa['data']['nama'] ?? '',
                    'tahun_masuk' => $mahasiswa['data']['tahun_masuk'] ?? '',
                    'prodi' => $mahasiswa['data']['prodi'] ?? '',
                ];       

                return view('konseling.riwayat_konseling', compact('dataMahasiswa'));
            }
            
            return redirect()->back()->withErrors(['error' => 'Gagal mengambil data mahasiswa dari API.']);
        } catch (Exception $e) {
            Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
    