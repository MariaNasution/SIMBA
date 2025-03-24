<?php

namespace App\Http\Controllers;

use App\Models\RequestKonseling;
use App\Models\RiwayatDaftarRequestKonseling;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MahasiswaKonselingController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data mahasiswa dari session
        $user = session('student_data');

        if (!$user || !isset($user['nim'])) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Ambil data yang masih pending dari RequestKonseling
        $pendingRequests = RequestKonseling::where('nim', $user['nim'])
            ->where('status', 'pending')
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        // Ambil request hanya milik mahasiswa yang sedang login
        $processedRequests = RiwayatDaftarRequestKonseling::where('nim', $user['nim'])
            ->orderBy('tanggal_pengajuan', 'desc')
            ->get();

        // Gabungkan kedua koleksi data
        $konselings = $pendingRequests->merge($processedRequests);

        // Sortir berdasarkan tanggal pengajuan (descending)
        $konselings = $konselings->sortByDesc('tanggal_pengajuan');

        // Konversi ke paginator manual agar tetap bisa dipakai di blade dengan pagination
        $currentPage = request()->input('page', 1);
        $perPage = 7;
        $konselings = new \Illuminate\Pagination\LengthAwarePaginator(
            $konselings->forPage($currentPage, $perPage),
            $konselings->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return view('mahasiswa.mahasiswa_konseling', compact('konselings'));
    }
}
