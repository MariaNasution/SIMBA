<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\RequestKonseling;

class adminController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();

        return view('beranda.homeAdmin', compact('pengumuman'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sumber' => 'required|string|max:50',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        Pengumuman::create([
            'sumber' => $request->sumber,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('admin')->with('success', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        try {
            $pengumuman = Pengumuman::findOrFail($id);
            $pengumuman->delete();

            return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus pengumuman.']);
        }
    }


    public function show($id)
    {
        // Ambil pengumuman berdasarkan ID
        $pengumuman = Pengumuman::findOrFail($id);

        // Kembalikan ke view dengan pengumuman yang ditemukan
        return view('beranda.detailpengumuman', compact('pengumuman'));
    }



    public function hasilKonseling()
    {
        return view('konseling.hasil_konseling');
    }

    public function riwayatKonseling()
    {
        return view('konseling.riwayat_konseling');
    }

    public function konselingLanjutan()
    {
        $apiToken = session('api_token');

        if (!$apiToken) {
            return redirect()->back()->withErrors(['error' => 'API token tidak tersedia']);
        }

        try {
            // Ambil data mahasiswa dari API
            $mahasiswaResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->get('https://cis-dev.del.ac.id/api/library-api/mahasiswa');

            if ($mahasiswaResponse->successful()) {
                $mahasiswas = $mahasiswaResponse->json()['data']['mahasiswa'];

                return view('konseling.konseling_lanjutan', compact('mahasiswas'));
            }

            return redirect()->back()->withErrors(['error' => 'Gagal mengambil data mahasiswa dari API.']);
        } catch (\Exception $e) {
            Log::error('Exception terjadi:', ['message' => $e->getMessage()]);
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function ajukanKonseling()
    {
        return view('konseling.ajukan_konseling');
    }

    public function daftarRequest()
    {
        // Ambil data request konseling dengan informasi mahasiswa
        $requests = RequestKonseling::with('mahasiswa')->where('status', 'pending')->get();

        // Kirim ke view
        return view('konseling.daftar_request', compact('requests'));
    }


}
