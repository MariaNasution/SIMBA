<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestKonseling;

class DaftarRequestKonselingController extends Controller
{
    public function daftarRequest()
    {
        // Ambil semua request konseling dengan relasi mahasiswa
        $requests = RequestKonseling::with('mahasiswa')->where('status', 'pending')->get();

        // Kirim data ke view
        return view('konseling.daftar_request', compact('requests'));
    }


    public function approve($id)
    {
        $request = RequestKonseling::findOrFail($id);
        $request->update(['status' => 'approved']);

        return redirect()->route('konseling.daftar_request')->with('success', 'Request berhasil disetujui.');
    }

    public function reject($id)
    {
        $request = RequestKonseling::findOrFail($id);
        $request->update(['status' => 'rejected']);

        return redirect()->route('konseling.daftar_request')->with('success', 'Request berhasil ditolak.');
    }

}
