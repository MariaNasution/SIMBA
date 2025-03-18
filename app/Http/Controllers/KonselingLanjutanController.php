<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KonselingLanjutan;
use App\Models\HasilKonseling;

class KonselingLanjutanController extends Controller
{
    public function index()
    {
+

        // Mengambil semua data dari tabel hasil_konseling
        $hasilKonseling = HasilKonseling::all();
        
        $mahasiswas = KonselingLanjutan::all();
        // Kirim data ke view
        return view('laporan.konseling', compact('hasilKonseling', 'mahasiswas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        // Ambil hasil konseling terbaru berdasarkan NIM
        $hasilKonseling = HasilKonseling::where('nim', $request->nim)
                               ->latest()
                               ->first();

                              
        if (!$hasilKonseling) {
            return redirect()->back()->with('error', 'Data hasil konseling tidak ditemukan.');
        }
        // Simpan data ke tabel konseling_lanjutans
        KonselingLanjutan::create([
            'nama' => $hasilKonseling->nama,
            'nim' => $hasilKonseling->nim,
            'keterangan' => $hasilKonseling->keterangan ?? 'Tidak ada keterangan',
        ]);

        return redirect()->route('konseling_lanjutan')
                     ->with('success', 'Data konseling lanjutan berhasil disimpan.');
    }
}
