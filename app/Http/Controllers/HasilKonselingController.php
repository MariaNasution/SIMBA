<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilKonseling;
use App\Models\RequestKonseling;
use Illuminate\Support\Facades\Storage;

class HasilKonselingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'request_konseling_id' => 'required|exists:request_konseling,id',
            'file' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:60000',
            'keterangan' => 'nullable|string',
        ]);

        // Simpan file ke storage
        $filePath = $request->file('file')->store('konseling_files', 'public');

        // Simpan ke database
        HasilKonseling::create([
            'request_konseling_id' => $request->request_konseling_id,
            'nama' => $request->nama,  // Pastikan nama dikirim
            'nim' => $request->nim,    // Pastikan nim dikirim
            'file' => $filePath,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }
}
