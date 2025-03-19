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

        // Simpan file ke storage dalam folder 'konseling_files'
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('konseling_files', $fileName, 'public');

        // Simpan ke database
        HasilKonseling::create([
            'request_konseling_id' => $request->request_konseling_id,
            'nama' => $request->nama,  // Pastikan nama dikirim
            'nim' => $request->nim,    // Pastikan nim dikirim
            'file' => $fileName,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');
    }
}
