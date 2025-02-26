<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AjukanKonselingController extends Controller
{
    public function index()
    {
        return view('konseling.ajukan_konseling');
        
    }
}

