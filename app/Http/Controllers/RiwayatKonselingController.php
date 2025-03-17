<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilKonseling;

class RiwayatKonselingController extends Controller
{
    // Menampilkan daftar hasil konseling unik berdasarkan NIM
    public function index()
    {
        $hasilKonseling = HasilKonseling::select('nim', 'nama')
            ->groupBy('nim', 'nama')
            ->get();

        return view('konseling.riwayat_konseling', compact('hasilKonseling'));
    }

    // Mencari riwayat konseling berdasarkan NIM atau Nama, tampilkan satu data per NIM
    public function CariRiwayatMahasiswa(Request $request)
    {
        $nim = $request->input('nim');
        $nama = $request->input('nama');

        $hasilKonseling = HasilKonseling::select('nim', 'nama')
            ->when($nim, function ($query, $nim) {
                return $query->where('nim', 'like', "%$nim%");
            })
            ->when($nama, function ($query, $nama) {
                return $query->where('nama', 'like', "%$nama%");
            })
            ->groupBy('nim', 'nama')
            ->get();

        return view('konseling.riwayat_konseling', compact('hasilKonseling'));
    }

    // Menampilkan detail hasil konseling berdasarkan NIM dari tabel hasil_konseling
    public function detail($nim)
    {
        // Ambil data hasil konseling berdasarkan NIM
        $hasilKonseling = HasilKonseling::where('nim', $nim)->get();

        // Ambil nama mahasiswa dari hasil konseling pertama (jika ada data)
        $nama = $hasilKonseling->first()->nama ?? 'Nama tidak ditemukan';

        return view('konseling.riwayat_konseling_detail', compact('nama', 'nim', 'hasilKonseling'));
    }
}
