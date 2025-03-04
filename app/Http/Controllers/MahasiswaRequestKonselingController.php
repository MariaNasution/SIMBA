<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestKonseling; // Pastikan model ini sesuai dengan tabel request_konseling

class MahasiswaRequestKonselingController extends Controller
{
    public function create()
    {
        return view('mahasiswa.mhs_konseling_request');
    }

    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'tanggal_pengajuan' => 'required|date',
            'deskripsi_pengajuan' => 'required|string',
        ]);

        // Simpan data ke database
        RequestKonseling::create($validatedData);

        return redirect()->route('mhs_konseling_request')->with('success', 'Request konseling berhasil dikirim.');
    }

    public function index()
    {
        // Ambil semua request konseling
        $requests = RequestKonseling::all();

        return view('daftar_request_konseling', compact('requests'));
    }
}