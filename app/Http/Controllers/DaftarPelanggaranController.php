<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DaftarPelanggaranController extends Controller
{
    public function index()
    {
        return view('konseling.daftar_pelanggaran');
        
    }
}
