<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\RequestKonseling;
use Exception;
use App\Services\NotificationService;

class AjukanKonselingController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        // Initialize empty data
        $dataMahasiswa = [];
        return view('konseling.ajukan_konseling', compact('dataMahasiswa'));
    }

    public function cariMahasiswa(Request $request)
    {
        $nim = $request->input('nim');
        $nama = $request->input('nama');
        $apiToken = session('api_token');

        if (!$apiToken) {
            return redirect()->back()->withErrors(['error' => 'API token tidak tersedia']);
        }

        try {
            Log::info('Mengambil data mahasiswa dari API', ['nim' => $nim, 'nama' => $nama]);

            // Prepare query parameters
            $queryParams = [];
            if (!empty($nim)) {
                $queryParams['nim'] = $nim;
            }
            if (!empty($nama)) {
                $queryParams['nama'] = $nama;
            }

            // If both are empty, redirect back with error
            if (empty($queryParams)) {
                return redirect()->back()->withErrors(['error' => 'Mohon masukkan NIM atau Nama untuk pencarian']);
            }

            // Get student data from API with filters
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/mahasiswa', $queryParams);

            if ($response->successful()) {
                $hasil = $response->json();
                $daftarMahasiswa = [];

                if (!empty($hasil['data']['mahasiswa'])) {
                    Log::info('Data mahasiswa ditemukan', ['jumlah' => count($hasil['data']['mahasiswa'])]);

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

                return view('konseling.ajukan_konseling', compact('daftarMahasiswa', 'nim', 'nama'));
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
            // Simpan pengajuan konseling
            $konseling = RequestKonseling::create([
                'nim' => $request->input('nim'),
                'nama_mahasiswa' => $request->input('nama'),
                'tanggal_pengajuan' => $request->input('tanggal_pengajuan'),
                'deskripsi_pengajuan' => $request->deskripsi ?? '',
                'status' => 'approved',
            ]);

            // Buat notifikasi untuk pengguna yang bersangkutan
            $nim = $request->input('nim');
            $message = 'Anda telah diajukan untuk melakukan konseling.';
            $type = 'konseling';
            $data = [
                'action' => 'create',
                'data' => $konseling->toArray()
            ];

            $this->notificationService->createNotification(
            $nim,
            $type,
            $message,
            $data,
        );

                // Redirect based on user role
                if (session('user.role') == 'kemahasiswaan') {
                    return redirect()->route('kemahasiswaan_konseling.ajukan')->with('success', 'Berhasil mengajukan konseling');
                } else {
                    return redirect()->route('konselor_konseling.ajukan')->with('success', 'Berhasil mengajukan konseling');
                }
            } catch (Exception $e) {
                Log::error('Exception saat mengajukan konseling:', ['message' => $e->getMessage()]);
                return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
            }
    }
}