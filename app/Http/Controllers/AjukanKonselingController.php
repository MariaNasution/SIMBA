<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;


class AjukanKonselingController extends Controller
{
    public function index()
    {
        $user = session('user');
        $nim = $user['nim'] ?? null;
        $apiToken = session('api_token');
        
        // Initial empty data for the view
        $dataMahasiswa = [];
        
        return view('konseling.ajukan_konseling', compact('dataMahasiswa', 'nim'));
    }
    
    public function cariMahasiswa(Request $request)
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

                return view('konseling.ajukan_konseling', compact('dataMahasiswa', 'nim'));
            }
            
            return redirect()->back()->withErrors(['error' => 'Gagal mengambil data mahasiswa dari API.']);
        } catch (Exception $e) {
            Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
