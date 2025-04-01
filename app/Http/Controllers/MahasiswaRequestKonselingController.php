<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestKonseling;
use Illuminate\Support\Facades\Auth;

class MahasiswaRequestKonselingController extends Controller
{
    public function create()
    {
        $user = session('student_data');
        //dd($user); // Debugging untuk melihat isi session
        return view('mahasiswa.mhs_konseling_request', compact('user'));
    }
    

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'tanggal_pengajuan' => 'required|date',
            'deskripsi_pengajuan' => 'required|string',
        ]);
    
        // Ambil data mahasiswa dari sesi
        $user = session('student_data');
    
        if (!$user) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan. Silakan hubungi administrator.');
        }
    
        // Simpan data ke database
        RequestKonseling::create([
            'nim' => $user['nim'],
            'nama_mahasiswa' => $user['nama'],
            'tanggal_pengajuan' => $request->tanggal_pengajuan,
            'deskripsi_pengajuan' => $request->deskripsi_pengajuan,
            'status' => 'pending',
        ]);
    
        return redirect()->route('mhs_konseling_request')->with('success', 'Request Konseling berhasil diajukan.');
    }
    

    public function index()
{
    // Ambil data mahasiswa dari session
    $user = session('student_data');

    if (!$user || !isset($user['nim'])) {
        return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
    }

    // Ambil request hanya milik mahasiswa yang sedang login berdasarkan NIM
    $requests = RequestKonseling::where('nim', $user['nim'])->get();

    return view('mahasiswa.daftar_request_konseling', compact('requests'));
}

}
