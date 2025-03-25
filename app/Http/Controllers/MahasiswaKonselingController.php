<?php

namespace App\Http\Controllers;

use App\Models\RequestKonseling;
use App\Models\RiwayatDaftarRequestKonseling;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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

        // Query data dari RequestKonseling (hanya yang masih pending)
        $pendingRequests = collect(); // Koleksi kosong
        if ($status === '' || $status === 'pending') {
            $pendingRequests = RequestKonseling::where('nim', $user['nim'])
                ->where('status', 'pending')
                ->orderBy('tanggal_pengajuan', 'desc')
                ->get();
        }

        // Query data dari RiwayatDaftarRequestKonseling (yang sudah diproses)
        $processedRequests = collect(); // Koleksi kosong
        if ($status === '' || in_array($status, ['approved', 'rejected'])) {
            $processedRequests = RiwayatDaftarRequestKonseling::where('nim', $user['nim'])
                ->when(in_array($status, ['approved', 'rejected']), function ($query) use ($status) {
                    return $query->where('status', $status);
                })
                ->orderBy('tanggal_pengajuan', 'desc')
                ->get();
        }

        // Gabungkan kedua koleksi data
        $konselings = $pendingRequests->merge($processedRequests);

        // Sortir berdasarkan tanggal pengajuan (descending)
        $konselings = $konselings->sortByDesc('tanggal_pengajuan');

        // Konversi ke paginator manual agar tetap bisa dipakai di blade dengan pagination
        $currentPage = request()->input('page', 1);
        $perPage = 7;
        $konselings = new LengthAwarePaginator(
            $konselings->forPage($currentPage, $perPage),
            $konselings->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return view('mahasiswa.mahasiswa_konseling', compact('konselings'));
    }
}
