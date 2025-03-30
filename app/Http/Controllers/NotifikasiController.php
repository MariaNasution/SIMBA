<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Auth; // jika menggunakan Auth atau ambil dari session

class NotifikasiController extends Controller
{
    public function markAllRead(Request $request)
    {
        // Misalnya, kita mengambil nim mahasiswa dari request atau session
        $nim = $request->input('nim');
        if (!$nim) {
            return response()->json(['message' => 'NIM not provided'], 400);
        }

        // Update semua notifikasi yang belum dibaca untuk mahasiswa tersebut
        Notifikasi::where('nim', $nim)->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['message' => 'Notifikasi telah ditandai sebagai dibaca.']);
    }
}
