<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IKController extends Controller
{
    public function index(Request $request)
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

            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false]) // Abaikan verifikasi SSL
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-izin-keluar', [
                    'nim' => $nim,
                    'start_date' => '',
                    'end_date' => '',
                    'status_request' => '',
                    'limit' => '',
                ]);

            Log::info('API Response', ['status' => $response->status(), 'body' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();
                $izinKeluar = $data['data'] ?? [];

                // Pagination manual
                $perPage = 10;
                $currentPage = $request->input('page', 1); // Ambil halaman saat ini dari input
                $offset = ($currentPage - 1) * $perPage;

                $paginatedData = array_slice($izinKeluar, $offset, $perPage);
                $total = count($izinKeluar);

                return view('perizinan.izin_keluar', [
                    'izinKeluar' => $paginatedData,
                    'total' => $total,
                    'currentPage' => $currentPage,
                    'perPage' => $perPage,
                ]);
            }

            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data izin keluar dari API.']);
        } catch (\Exception $e) {
            Log::error('Kesalahan API:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
