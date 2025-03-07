<?php

namespace App\Http\Controllers;

use App\Models\RequestKonseling;
use Illuminate\Http\Request;

class MahasiswaKonselingController extends Controller
{
    public function index()
    {
        // Ambil data mahasiswa dari session
        $user = session('student_data');

        if (!$user || !isset($user['nim'])) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Ambil request hanya milik mahasiswa yang sedang login
        $konselings = RequestKonseling::where('nim', $user['nim'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->paginate(10);

        // Debugging
       // dd($user, $konselings);

        return view('mahasiswa.mahasiswa_konseling', compact('konselings'));
    }
}