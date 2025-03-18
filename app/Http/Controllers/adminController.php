<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\RequestKonseling;
use App\Models\KonselingLanjutan;

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

    public function konselingLanjutan(Request $request)
    {
        try {
            // Ambil parameter pencarian
            $nim = $request->input('nim');
            $nama = $request->input('nama');

            // Query untuk mencari data berdasarkan NIM atau nama
            $mahasiswas = KonselingLanjutan::query()
                ->when($nim, function ($query, $nim) {
                    return $query->where('nim', 'like', "%$nim%");
                })
                ->when($nama, function ($query, $nama) {
                    return $query->where('nama', 'like', "%$nama%");
                })
                ->get();
            return view('konseling.konseling_lanjutan', compact('mahasiswas'));
        } catch (\Exception $e) {
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
    public function detail($nim)
    {
        // Ambil data hasil konseling berdasarkan NIM
        $mahasiswas = KonselingLanjutan::where('nim', $nim)->get();

        // Ambil nama mahasiswa dari hasil konseling pertama (jika ada data)
        $nama = $mahasiswas->first()->nama ?? 'Nama tidak ditemukan';

        return view('konseling.konseling_lanjutan_detail', compact('nama', 'nim', 'mahasiswas'));
    }
}


