<?php

namespace App\Http\Controllers;
use App\Models\RequestKonseling;

use Illuminate\Http\Request;

class MahasiswaKonselingController extends Controller
{
    //
    public function index()
    {
        $konselings = RequestKonseling::orderBy('tanggal_pengajuan', 'desc')->paginate(10);
        return view('mahasiswa.mahasiswa_konseling', compact('konselings'));
        
    }
}
