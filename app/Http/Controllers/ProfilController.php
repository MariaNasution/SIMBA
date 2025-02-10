<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProfilController extends Controller
{
    public function index()
    {
        $user = session('user');
        $apiToken = session('api_token');

        try {
            // Ambil data mahasiswa menggunakan token API
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                    'nim' => $user['nim'],
                ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Ambil data dari kunci `data` dalam respons
                if (isset($responseData['data'])) {
                    $student = $responseData['data'];
                    return view('profil.profil', compact('student', 'user'));
                }
            }

            Log::error('Profil API error:', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data profil.']);
        } catch (\Exception $e) {
            Log::error('API Error:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan saat menghubungi API.']);
        }
    }
}
