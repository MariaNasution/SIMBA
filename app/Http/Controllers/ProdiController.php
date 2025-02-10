<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProdiController extends Controller
{
    public function index()
    {
        $user = session('user'); // Ambil data user dari session
        $nim = $user['nim'] ?? null;

        if (!$nim) {
            return redirect()->route('beranda')->withErrors(['error' => 'NIM tidak ditemukan dalam session.']);
        }

        // Tentukan ID program berdasarkan NIM
        $programId = null;
        if (str_starts_with($nim, '11S')) {
            $programId = 1;
        } elseif (str_starts_with($nim, '12S')) {
            $programId = 2;
        } elseif (str_starts_with($nim, '14S')) {
            $programId = 3;
        } elseif (str_starts_with($nim, '114')) {
            $programId = 4;
        } elseif (str_starts_with($nim, '113')) {
            $programId = 5;
        } elseif (str_starts_with($nim, '133')) {
            $programId = 6;
        } elseif (str_starts_with($nim, '31S')) {
            $programId = 7;
        } elseif (str_starts_with($nim, '21S')) {
            $programId = 8;
        } elseif (str_starts_with($nim, '22S')) {
            $programId = 9;
        }

        if (!$programId) {
            return redirect()->route('beranda')->withErrors(['error' => 'Program tidak ditemukan untuk NIM tersebut.']);
        }

        // Ambil data program berdasarkan ID
        $program = DB::table('programs')->find($programId);

        if (!$program) {
            return redirect()->route('beranda')->withErrors(['error' => 'Data program tidak ditemukan di database.']);
        }

        return view('perkuliahan.prodi', compact('program'));
    }
}
