<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KeasramaanController extends Controller
{
    public function index()
    {
        return view('beranda.homeKeasramaan');
    }

    public function pelanggaran()
    {
        return view('catatanPerilaku.pelanggaran_keasramaan');
    }

    public function presensi()
    {
        return view('dosen.presensi');
    }
}