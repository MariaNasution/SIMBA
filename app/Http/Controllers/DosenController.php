<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DosenController extends Controller
{
    public function index()
    {
        return view('beranda.homeDosen');
    }

    public function presensi()
    {
        return view('dosen.presensi');
    }
}