<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function beranda()
    {
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }
    return view('beranda.homeDosen');
    }

    /**
     * Display the perwalian page (list of anak wali).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    
    public function index()
    {
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $dosenUsername = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenUsername)
            ->where('role', 'mahasiswa')
            ->select('username')
            ->get()
            ->pluck('username')
            ->toArray();

        return view('dosen.index', compact('anakWali'));
    }

    /**
     * Display the presensi page for dosen.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
public function presensi()
{
        return view('dosen.presensi');
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $dosenUsername = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenUsername)
            ->where('role', 'mahasiswa')
            ->select('username')
            ->get();

        return view('dosen.presensi', compact('anakWali'));
}
}