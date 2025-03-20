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
            'request_konseling_id' => 'required|exists:hasil_konseling,id', // ID dari konseling yang spesifik
        ]);
    
        // Ambil hasil konseling berdasarkan ID yang diberikan
        $hasilKonseling = HasilKonseling::find($request->request_konseling_id);

                              
        if (!$hasilKonseling) {
            return redirect()->back()->with('error', 'Data hasil konseling tidak ditemukan.');
        }

          // Update the status of the original record
            $hasilKonseling->status = 'continued';
            $hasilKonseling->save();

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
