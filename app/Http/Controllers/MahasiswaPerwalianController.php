<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MahasiswaPerwalianController extends Controller
{
    public function index()
    {
        return view('mahasiswa.mahasiswa_perwalian');
        
    }
}
