<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilKonseling;
use App\Models\Mahasiswa;

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

    // Menampilkan detail hasil konseling berdasarkan NIM
    public function detail($nim)
    {
        // Ambil data mahasiswa berdasarkan NIM
        $mahasiswa = Mahasiswa::where('nim', $nim)->firstOrFail();

        // Ambil hasil konseling berdasarkan NIM
        $hasilKonseling = HasilKonseling::where('nim', $nim)->get();

        return view('konseling.riwayat_konseling_detail', compact('mahasiswa', 'hasilKonseling'));
    }

}
