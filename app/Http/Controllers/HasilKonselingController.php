<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MahasiswaKonseling;
use Illuminate\Support\Facades\Storage;

class HasilKonselingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'required|string|unique:mahasiswa_konseling,nim|max:20',
            'file' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:12000',
            'keterangan' => 'nullable|string',
        ]);

        // Simpan file ke storage
        $filePath = $request->file('file')->store('konseling_files', 'public');

        // Simpan ke database
        MahasiswaKonseling::create([
            'nama' => $request->nama,
            'nim' => $request->nim,
            'file' => $filePath,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', 'Data berhasil disimpan.');
    }
}
