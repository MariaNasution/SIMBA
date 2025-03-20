<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestKonseling;

class DaftarRequestKonselingController extends Controller
{
    public function daftarRequest()
    {
        // Ambil request konseling dengan status pending dan paginasi 5 data per halaman
        $requests = RequestKonseling::where('status', 'pending')->paginate(7);

        // Kirim data ke view
        return view('konseling.daftar_request', compact('requests'));
    }

    public function approve($id)
    {
        $request = RequestKonseling::findOrFail($id);
        $request->update(['status' => 'approved']);

        return redirect()->route('hasil_konseling')->with('success', 'Request berhasil disetujui.');
    }

    public function reject($id)
    {
        $request = RequestKonseling::findOrFail($id);
        $request->update(['status' => 'rejected']);

        return redirect()->route('daftar_request')->with('error', 'Request berhasil ditolak.');
    }
}
