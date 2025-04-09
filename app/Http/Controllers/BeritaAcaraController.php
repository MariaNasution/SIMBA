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

        // Fetch classes associated with the user
        $classes = [];
        $dosenRecord = DB::table('dosen_wali')
            ->where('username', $user['username'])
            ->first();
        if ($dosenRecord && !empty($dosenRecord->kelas)) {
            $classes = array_map('trim', explode(',', $dosenRecord->kelas));
        }
        Log::info('Classes fetched for user', ['username' => $user['username'], 'classes' => $classes]);

        // Fetch all completed perwalians for these classes
        $completedPerwalians = Perwalian::whereIn('kelas', $classes)
            ->where('ID_Dosen_Wali', $user['nip'])
            ->where('Status', 'Completed')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('absensi')
                    ->whereColumn('absensi.ID_Perwalian', 'perwalian.ID_Perwalian');
            })
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('berita_acaras')
                    ->whereColumn('berita_acaras.tanggal_perwalian', 'perwalian.Tanggal')
                    ->whereColumn('berita_acaras.kelas', 'perwalian.kelas')
                    ->where('berita_acaras.user_id', $user['user_id']);
            })
            ->get()
            ->map(function ($perwalian) {
                return [
                    'class' => $perwalian->kelas,
                    'date' => $perwalian->Tanggal,
                    'angkatan' => $perwalian->angkatan,
                    'display' => "Kelas {$perwalian->kelas} - " . Carbon::parse($perwalian->Tanggal)->translatedFormat('l, d F Y'),
                ];
            })->toArray();

        Log::info('Completed perwalians fetched', [
            'count' => count($completedPerwalians),
            'data' => $completedPerwalians
        ]);
        return view('perwalian.berita_acara_select_class', compact('completedPerwalians'));
    }

    public function create(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Anda harus login untuk mengakses berita acara.']);
        }
        $selectedClass = $request->query('kelas');
        $selectedDate = $request->query('tanggal_perwalian');
        $selectedAngkatan = $request->query('angkatan');

        if (!$selectedClass || !$selectedDate) {
            return redirect()->route('berita_acara.select_class')->withErrors(['error' => 'Kelas dan tanggal perwalian harus dipilih.']);
        }

        // Fetch the perwalian to ensure it exists and meets criteria
        $perwalian = Perwalian::where('kelas', $selectedClass)
            ->where('Tanggal', $selectedDate)
            ->where('ID_Dosen_Wali', $user['nip'])
            ->where('Status', 'Completed')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('absensi')
                    ->whereColumn('absensi.ID_Perwalian', 'perwalian.ID_Perwalian');
            })
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('berita_acaras')
                    ->whereColumn('berita_acaras.tanggal_perwalian', 'perwalian.Tanggal')
                    ->whereColumn('berita_acaras.kelas', 'perwalian.kelas')
                    ->where('berita_acaras.user_id', $user['user_id']);
            })
            ->first();
        if (!$perwalian) {
            return redirect()->route('berita_acara.select_class')->withErrors(['error' => 'Perwalian ini tidak valid atau sudah memiliki berita acara.']);
        }

        // Fetch absensi records
        $absensiRecords = Absensi::where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->with('mahasiswa')
            ->get();

        Log::info('Absensi records fetched for form', [
            'ID_Perwalian' => $perwalian->ID_Perwalian,
            'count' => $absensiRecords->count()
        ]);

        return view('perwalian.berita_acara', compact('selectedClass', 'selectedDate', 'selectedAngkatan', 'absensiRecords', 'perwalian'));
    }

    public function store(Request $request)
    {
        $user = session('user');
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Anda harus login untuk membuat berita acara.'], 401);
        }

        // Log the incoming request data
        Log::info('Store request received in BeritaAcaraController', [
            'request_data' => $request->all(),
            'user' => $user,
        ]);

        $selectedClass = $request->kelas;
        $selectedDate = $request->tanggal_perwalian;

        // Verify the perwalian session
        $perwalian = Perwalian::where('kelas', $selectedClass)
            ->where('Tanggal', $selectedDate)
            ->where('ID_Dosen_Wali', $user['nip'])
            ->where('Status', 'Completed')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('absensi')
                    ->whereColumn('absensi.ID_Perwalian', 'perwalian.ID_Perwalian');
            })
            ->whereNotExists(function ($query) use ($user) {
                $query->select(DB::raw(1))
                    ->from('berita_acaras')
                    ->whereColumn('berita_acaras.tanggal_perwalian', 'perwalian.Tanggal')
                    ->whereColumn('berita_acaras.kelas', 'perwalian.kelas')
                    ->where('berita_acaras.user_id', $user['user_id']);
            })
            ->first();

        // Log the result of the Perwalian query
        Log::info('Perwalian check in store method', [
            'selectedClass' => $selectedClass,
            'selectedDate' => $selectedDate,
            'user_nip' => $user['nip'],
            'perwalian_found' => $perwalian ? true : false,
            'perwalian_data' => $perwalian ? $perwalian->toArray() : null,
        ]);

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
            ],
            'perihal_perwalian' => 'required|string|max:255',
            'agenda' => 'required|string',
            'hari_tanggal' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'perihal2' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'tanggal_ttd' => [
                'required',
                'date',
                'date_format:Y-m-d',
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
            Log::info("Value of berita acara", ['berita_acara' => $beritaAcara->toArray()]);
            return response()->json([
                'success' => true,
                'message' => 'Berita acara berhasil disimpan.',
                'kelas' => $request->kelas,
                'tanggal_perwalian' => $request->tanggal_perwalian,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create Berita Acara: ' . $e->getMessage(), ['exception' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan berita acara: ' . $e->getMessage(),
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

    public function success($kelas, $tanggal_perwalian)
    {
        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Anda harus login untuk melihat halaman ini.']);
        }

        return view('perwalian.berita_acara_success', compact('kelas', 'tanggal_perwalian'));
    }
}