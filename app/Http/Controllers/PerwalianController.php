<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerwalianController extends Controller
{
    public function jadwal()
    {
        return view('perwalian.perwalian_jadwal');
    }

    public function kelas()
    {
        return view('perwalian.perwalian_kelas');
    }

    public function beritaAcara()
    {
        return view('perwalian.perwalian_berita_acara');
    }
}
