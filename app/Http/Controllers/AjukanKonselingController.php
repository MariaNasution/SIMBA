<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\RequestKonseling; 
use Exception;

class AjukanKonselingController extends Controller
{
    public function index()
    {
        // Initialize empty data
        $dataMahasiswa = [];
        return view('konseling.ajukan_konseling', compact('dataMahasiswa'));
    }

    public function cariMahasiswa(Request $request)
    {
        $nama = $request['keyword']; 
        $apiToken = session('api_token');
        

        if (!$apiToken) {
            return redirect()->back()->withErrors(['error' => 'API token tidak tersedia']);
        }

        try {
            Log::info('Mengambil data mahasiswa dari API dengan filter nama', ['nama' => $nama]);

            // Get student data from API with name filter
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/mahasiswa', [
                    'nama' => $nama, 
                ]);
                // dd($response->json());

            if ($response->successful()) {
                $hasil = $response->json();
                $daftarMahasiswa = [];

                if (!empty($hasil['data']['mahasiswa'])) {
                    Log::info('Data mahasiswa ditemukan', ['data' => $hasil['data']]); // Log untuk debug
                
                    foreach ($hasil['data']['mahasiswa'] as $mahasiswa) {
                        $daftarMahasiswa[] = [
                            'nim' => $mahasiswa['nim'] ?? 'N/A',
                            'nama' => $mahasiswa['nama'] ?? '',
                            'tahun_masuk' => $mahasiswa['angkatan'] ?? '',
                            'prodi' => $mahasiswa['prodi_name'] ?? '',
                        ];
                    }
                 

                    
                } else {
                    Log::warning('Tidak ada data mahasiswa ditemukan');
                }
                
                return view('konseling.ajukan_konseling', compact('daftarMahasiswa', 'nama'));
            }

            return redirect()->back()->withErrors(['error' => 'Gagal mengambil data mahasiswa dari API.']);
        } catch (Exception $e) {
            Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function pilihMahasiswa(Request $request)
    {
        $dataMahasiswa = [
            'nim' => $request->input('nim'),
            'nama' => $request->input('nama'),
            'tahun_masuk' => $request->input('tahun_masuk'),
            'prodi' => $request->input('prodi'),
        ];

        return view('konseling.ajukan_konseling', compact('dataMahasiswa'));
    }

    public function ajukanKonseling(Request $request)
    {
        $request->validate([
            'nim' => 'required',
            'tanggal_pengajuan' => 'required|date',
            'deskripsi_pengajuan' => 'nullable|string',
        ]);
    
        try {
            
            RequestKonseling::create([
                'nim' => $request->input('nim'),
                'nama_mahasiswa' => $request->input('nama'),
                'tanggal_pengajuan' => $request->input('tanggal_pengajuan'),
                'deskripsi_pengajuan' =>$request->deskripsi ?? '',
                'status' => 'approved',
            ]);
    
            return redirect()->route('konseling.index')->with('success', 'Berhasil mengajukan konseling');
            
        } catch (Exception $e) {
            Log::error('Exception saat mengajukan konseling:', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}