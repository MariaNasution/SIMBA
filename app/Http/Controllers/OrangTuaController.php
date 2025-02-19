<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrangTuaController extends Controller
{
    public function index()
    {
        return view('beranda.homeOrangTua');
    }

    public function presensi()
    {
        return view('dosen.presensi');
    }
}