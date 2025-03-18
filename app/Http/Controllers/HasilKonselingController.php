<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilKonseling; // Perbaiki Model
use Illuminate\Support\Facades\Storage;

class HasilKonselingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'required', // Sesuaikan dengan migration
            'file' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:60000',
            'keterangan' => 'nullable|string',
        ]);

        // Simpan file ke storage
        $filePath = $request->file('file')->store('konseling_files', 'public');

        // Simpan ke database
        HasilKonseling::create([
            'nama' => $request->nama,
            'nim' => $request->nim,
            'file' => $filePath,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }
}
