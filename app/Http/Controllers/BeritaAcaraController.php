<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BeritaAcara;
use Illuminate\Support\Facades\Auth;

class BeritaAcaraController extends Controller
{
    public function index()
    {
        $berita_acaras = BeritaAcara::where('user_id', Auth::id())->get();
        return view('perwalian.berita_acara', compact('berita_acaras'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
        ]);

        BeritaAcara::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tanggal' => $request->tanggal,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('berita.acara')->with('success', 'Berita acara berhasil disimpan!');
    }
}
