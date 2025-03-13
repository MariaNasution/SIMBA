<?php

namespace App\Http\Controllers;

use App\Models\BeritaAcara;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BeritaAcaraController extends Controller
{
    // Menampilkan daftar berita acara (riwayat)
    public function index()
    {

        // $beritaAcaras = BeritaAcara::where('user_id', Auth::id())->latest()->get();
        $beritaAcaras = BeritaAcara::All();
        return view('berita_acara.index', compact('beritaAcaras'));
    }

    // Menampilkan form untuk membuat berita acara baru
    public function create()
    {
        return view('berita_acara.create');
    }

    // Menyimpan berita acara baru
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

        return redirect()->route('berita_acara.index')->with('success', 'Berita Acara berhasil disimpan.');
    }

    // Menampilkan detail berita acara
    public function show($id)
    {
        $beritaAcara = BeritaAcara::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('berita_acara.show', compact('beritaAcara'));
    }
}