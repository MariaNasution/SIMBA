<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BeritaAcara;
use App\Models\Absensi;
use App\Models\Perwalian;
use Carbon\Carbon;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BeritaAcaraController extends Controller
{
    public function selectClass(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Anda harus login untuk mengakses berita acara.']);
        }

        // Fetch classes associated with the user (e.g., from a dosen_wali table)
        $classes = [];
        $dosenRecord = DB::table('dosen_wali')
            ->where('username', $user['username'])
            ->first();

        if ($dosenRecord && !empty($dosenRecord->kelas)) {
            $classes = array_map('trim', explode(',', $dosenRecord->kelas));
        }

        $selectedClass = $request->query('kelas');
        $selectedDate = $request->query('tanggal_perwalian');

        // Fetch available finished perwalian sessions for the selected class
        $availablePerwalians = [];
        if ($selectedClass) {
            $availablePerwalians = Perwalian::where('kelas', $selectedClass)
                ->where('ID_Dosen_Wali', $user['nip'])
                ->where('Status', 'Completed') // Only completed perwalian sessions
                ->where('Tanggal', '<=', now()->format('Y-m-d')) // Only past or today
                ->whereHas('absensi') // Ensure absensi records exist
                // ->whereDoesntHave('beritaAcara', function ($query) use ($user) {
                //     $query->where('user_id', $user['user_id']);
                // }) // Exclude perwalian sessions that already have a Berita Acara
                ->get();
        }

        // Fetch absensi records for the selected perwalian session
        $absensiRecords = [];
        $perwalian = null;

        if ($selectedClass && $selectedDate) {
            $perwalian = Perwalian::where('kelas', $selectedClass)
                ->where('Tanggal', $selectedDate)
                ->where('ID_Dosen_Wali', $user['nip'])
                ->where('Status', 'Completed')
                ->where('Tanggal', '<=', now()->format('Y-m-d'))
                ->whereHas('absensi')
                ->whereDoesntHave('beritaAcara', function ($query) use ($user) {
                    $query->where('user_id', $user['user_id']);
                })
                ->first();

            if ($perwalian) {
                $absensiRecords = Absensi::where('ID_Perwalian', $perwalian->ID_Perwalian)
                    ->with('mahasiswa')
                    ->get();
            }
        }

        return view('perwalian.berita_acara', compact('classes', 'selectedClass', 'selectedDate', 'absensiRecords', 'availablePerwalians', 'perwalian'));
    }

    public function store(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Anda harus login untuk membuat berita acara.'], 401);
        }

        $selectedClass = $request->kelas;
        $selectedDate = $request->tanggal_perwalian;

        // Verify the perwalian session is completed and has absensi records
        $perwalian = Perwalian::where('kelas', $selectedClass)
            ->where('Tanggal', $selectedDate)
            ->where('ID_Dosen_Wali', $user['nip'])
            ->where('Status', 'Completed')
            ->where('Tanggal', '<=', now()->format('Y-m-d'))
            ->whereHas('absensi')
            ->whereDoesntHave('beritaAcara', function ($query) use ($user) {
                $query->where('user_id', $user['user_id']);
            })
            ->first();

        if (!$perwalian) {
            return response()->json([
                'success' => false,
                'message' => 'Perwalian ini belum selesai, tidak memiliki absensi, atau sudah memiliki berita acara.',
            ], 400);
        }

        $request->validate([
            'kelas' => 'required|string|max:255',
            'angkatan' => 'required|integer',
            'tanggal_perwalian' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:' . now()->format('Y-m-d'), // Must be in the past or today
            ],
            'perihal_perwalian' => 'required|string|max:255',
            'agenda' => 'required|string',
            'hari_tanggal' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:2025-01-01',
                'before_or_equal:2027-12-31',
                function ($attribute, $value, $fail) {
                    $selectedDate = Carbon::parse($value);
                    if ($selectedDate->isBefore(now()->startOfDay())) {
                        $fail('Hari/tanggal feedback tidak boleh di masa lalu.');
                    }
                },
            ],
            'perihal2' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'tanggal_ttd' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:2025-01-01',
                'before_or_equal:2027-12-31',
                function ($attribute, $value, $fail) {
                    $selectedDate = Carbon::parse($value);
                    if ($selectedDate->isBefore(now()->startOfDay())) {
                        $fail('Tanggal tanda tangan tidak boleh di masa lalu.');
                    }
                },
            ],
            'dosen_wali_ttd' => 'required|string|max:255',
        ]);

        try {
            $beritaAcara = BeritaAcara::create([
                'kelas' => $request->kelas,
                'angkatan' => $request->angkatan,
                'dosen_wali' => $user['username'],
                'tanggal_perwalian' => $request->tanggal_perwalian,
                'perihal_perwalian' => $request->perihal_perwalian,
                'agenda_perwalian' => $request->agenda,
                'hari_tanggal_feedback' => $request->hari_tanggal,
                'perihal_feedback' => $request->perihal2 ?? 'Tidak ada',
                'catatan_feedback' => $request->catatan,
                'tanggal_ttd' => $request->tanggal_ttd,
                'dosen_wali_ttd' => $request->dosen_wali_ttd,
                'user_id' => $user['user_id'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berita acara berhasil disimpan.',
                'kelas' => $request->kelas,
                'tanggal_perwalian' => $request->tanggal_perwalian,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Berita Acara: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan berita acara. Silakan coba lagi.',
            ], 500);
        }
    }

    public function show($id)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Anda harus login untuk melihat berita acara.']);
        }

        $beritaAcara = BeritaAcara::where('user_id', $user['user_id'])->findOrFail($id);
        return view('perwalian.berita_acara_detail', compact('beritaAcara'));
    }
}