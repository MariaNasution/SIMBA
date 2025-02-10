<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BursarController extends Controller
{
    public function index(Request $request)
    {
        // Fetch the API data as you are doing currently
        $user = session('user');
        $nim = $user['nim'] ?? null;
        $apiToken = session('api_token');

        if (!$nim) {
            return redirect()->route('beranda')->withErrors(['error' => 'NIM tidak ditemukan.']);
        }

        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-bursar-mhs', [
                    'nim' => $nim,
                ]);

            $response2 = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-virtual-account', [
                    'nim' => $nim,
                ]);

            if ($response->successful() && $response2->successful()) {
                $bursarData = $response->json()['data'] ?? [];
                $VAData = $response2->json()['data'] ?? [];

                // Add a unique identifier to each item
                foreach ($bursarData as $index => &$item) {
                    $item['id'] = $index; // Use the array index as the ID
                }

                // Pagination
                $perPage = 10;
                $currentPage = $request->input('page', 1);
                $offset = ($currentPage - 1) * $perPage;
                $paginatedData = array_slice($bursarData, $offset, $perPage);
                $total = count($bursarData);

                return view('bursar.bursar', [
                    'bursarData' => $paginatedData,
                    'total' => $total,
                    'currentPage' => $currentPage,
                    'perPage' => $perPage,
                    'VAData' => $VAData,
                ]);
            }

            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data bursar dari API.']);
        } catch (\Exception $e) {
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }


    public function showDetail($id)
    {
        $user = session('user');
        $nim = $user['nim'] ?? null;
        $apiToken = session('api_token');

        if (!$nim) {
            return redirect()->route('beranda')->withErrors(['error' => 'NIM tidak ditemukan.']);
        }

        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-bursar-mhs', [
                    'nim' => $nim,
                ]);

            $response2 = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/aktivitas-mhs-api/get-virtual-account', [
                    'nim' => $nim,
                ]);

            if ($response->successful() && $response2->successful()) {
                $bursarData = $response->json()['data'] ?? [];
                $VAData = $response2->json()['data'] ?? [];

                if (!isset($bursarData[$id])) {
                    return redirect()->route('bursar')->withErrors(['error' => 'Detail tidak ditemukan.']);
                }

                $bursarDetail = $bursarData[$id];

                return view('bursar.detail', [
                    'bursarDetail' => $bursarDetail,
                    'VAData' => $VAData,
                ]);
            }

            return redirect()->route('bursar')->withErrors(['error' => 'Gagal mengambil data bursar dari API.']);
        } catch (\Exception $e) {
            return redirect()->route('bursar')->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
