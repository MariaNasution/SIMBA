<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsramaController extends Controller
{
    public function index()
    {
        $user = session('user'); // Mendapatkan data user dari session
        $nim = $user['nim'] ?? null; // NIM user saat login
        $apiToken = session('api_token');

        if (!$nim) {
            Log::error('NIM tidak ditemukan dalam session.');
            return redirect()->route('beranda')->withErrors(['error' => 'NIM tidak ditemukan.']);
        }

        Log::info('NIM fetched from session', ['nim' => $nim]);

        try {
            // Fetch data asrama mahasiswa
            $asramaResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-asrama-mhs', [
                    'nim' => $nim,
                    'ta' => '',
                    'sem_ta' => '',
                ]);

            Log::info('Asrama API Response', ['status' => $asramaResponse->status(), 'body' => $asramaResponse->body()]);

            if (!$asramaResponse->successful()) {
                return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data asrama.']);
            }

            $asramaData = $asramaResponse->json()['data'] ?? [];
            $asramaName = $asramaData['asrama'] ?? 'Tidak Diketahui';

            // Fetch data pembina asrama
            $listAsramaResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-list-asrama');

            Log::info('List Asrama API Response', ['status' => $listAsramaResponse->status(), 'body' => $listAsramaResponse->body()]);

            if (!$listAsramaResponse->successful()) {
                return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data pembina asrama.']);
            }

            $listAsrama = collect($listAsramaResponse->json()['data'] ?? []);
            $pembinaAsrama = $listAsrama->firstWhere('nama', $asramaName)['keasramaan'] ?? 'Tidak Diketahui';

            return view('asrama.asrama', compact('asramaData', 'pembinaAsrama'));
        } catch (\Exception $e) {
            Log::error('Kesalahan API:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
