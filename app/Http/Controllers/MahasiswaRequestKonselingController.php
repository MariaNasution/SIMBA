<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MahasiswaRequestKonselingController extends Controller
{
    public function create()
    {
        return view('mahasiswa.mhs_konseling_request');
    }
}
