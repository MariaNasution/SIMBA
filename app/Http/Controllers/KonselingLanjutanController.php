<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KonselingLanjutan;

class KonselingLanjutanController extends Controller
{
    public function store(Request $request)
    {
        //dd($request->all());
        KonselingLanjutan::create([
            'nama' => $request->nama,
            'nim' => $request->nim,
        ]);

        return redirect()->back()->with('success', 'Data Konseling Lanjutan berhasil ditambahkan.');
    }
}