<?php

namespace App\Http\Controllers;

use App\Models\RequestKonseling;
use Illuminate\Http\Request;

class MahasiswaKonselingController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data mahasiswa dari session
        $user = session('student_data');

        if (!$user || !isset($user['nim'])) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Ambil filter status dari request
        $status = $request->query('status', '');

        // Query request konseling dengan filter status
        $query = RequestKonseling::where('nim', $user['nim'])->orderBy('tanggal_pengajuan', 'desc');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        // Paginasi data dengan mempertahankan filter status
        $konselings = $query->paginate(10)->appends(['status' => $status]);

        return view('mahasiswa.mahasiswa_konseling', compact('konselings'));
    }
}
