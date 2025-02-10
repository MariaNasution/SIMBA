<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class adminController extends Controller
{
    public function index()
    {
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();

        return view('beranda.homeAdmin', compact('pengumuman'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sumber' => 'required|string|max:50',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
        ]);

        Pengumuman::create([
            'sumber' => $request->sumber,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('admin')->with('success', 'Data berhasil disimpan!');
    }

    public function destroy($id)
    {
        try {
            $pengumuman = Pengumuman::findOrFail($id);
            $pengumuman->delete();

            return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menghapus pengumuman.']);
        }
    }


    public function show($id)
    {
        // Ambil pengumuman berdasarkan ID
        $pengumuman = Pengumuman::findOrFail($id);

        // Kembalikan ke view dengan pengumuman yang ditemukan
        return view('beranda.detailpengumuman', compact('pengumuman'));
    }
}
