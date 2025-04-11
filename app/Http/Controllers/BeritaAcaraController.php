<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\BeritaAcara;
use App\Models\Perwalian;
use App\Models\Absensi;
use App\Models\Mahasiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BeritaAcaraController extends Controller
{
    public function index()
    {
        return redirect()->route('berita_acara.select_class');
    }

    public function selectClass(Request $request)
    {
        $user = session('user');
        if (!$user) {
            Log::error('No user authenticated in BeritaAcaraController@selectClass');
            return redirect()->route('login')->withErrors(['error' => 'Anda harus login untuk mengakses berita acara.']);
        }

        $classes = [];
        $angkatanMap = [];
        $dosenRecord = DB::table('dosen_wali')
            ->where('username', $user['username'])
            ->first();
        if ($dosenRecord && !empty($dosenRecord->kelas)) {
            $classes = array_map('trim', explode(',', $dosenRecord->kelas));
            $angkatanList = array_map('trim', explode(',', $dosenRecord->angkatan));

            // Create a mapping of kelas to angkatan
            foreach ($classes as $index => $kelas) {
                $angkatan = isset($angkatanList[$index]) ? $angkatanList[$index] : end($angkatanList);
                $angkatanMap[$kelas] = $angkatan;
            }
        }
        Log::info('Classes and angkatan fetched for user in BeritaAcaraController@selectClass', [
            'username' => $user['username'],
            'classes' => $classes,
            'angkatan_map' => $angkatanMap,
        ]);

        if (empty($classes)) {
            Log::warning('No classes found for user in dosen_wali table', ['username' => $user['username']]);
            return view('perwalian.berita_acara_select_class', ['presentedPerwalians' => []])
                ->with('error', 'Tidak ada kelas yang terkait dengan akun Anda.');
        }

        // Fetch Perwalian records with Status "Presented" that have Absensi but no Berita Acara
        $presentedPerwalians = Perwalian::whereIn('kelas', $classes)
            ->where('ID_Dosen_Wali', $user['nip'])
            ->where('Status', 'Presented') // Only show "Presented" Perwalian
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
            ->orderBy('Tanggal', 'desc')
            ->get()
            ->map(function ($perwalian) use ($angkatanMap) {
                $date = Carbon::parse($perwalian->Tanggal);
                $kelas = $perwalian->kelas;
                $angkatan = $angkatanMap[$kelas] ?? $perwalian->angkatan; // Use mapped angkatan, fallback to perwalian
                return [
                    'id' => $perwalian->ID_Perwalian,
                    'class' => $kelas,
                    'angkatan' => $angkatan, // Include the determined angkatan
                    'date' => $date->format('Y-m-d'),
                    'display' => $kelas, // Only display the class name (e.g., "12IF1")
                ];
            })->toArray();

        Log::info('Presented Perwalians fetched for BeritaAcaraController@selectClass', [
            'count' => count($presentedPerwalians),
            'data' => $presentedPerwalians,
        ]);

        return view('perwalian.berita_acara_select_class', compact('presentedPerwalians'));
    }

    public function create(Request $request, $date, $class)
    {
        $user = session('user');
        if (!$user) {
            Log::error('No user authenticated in BeritaAcaraController@create');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        // Fetch Perwalian with Status "Presented"
        $perwalian = Perwalian::where('Tanggal', $date)
            ->where('kelas', $class)
            ->where('ID_Dosen_Wali', $user['nip'])
            ->where('Status', 'Presented') // Only allow "Presented" Perwalian
            ->first();

        if (!$perwalian) {
            Log::error('Perwalian not found or not in Presented status in BeritaAcaraController@create:', [
                'date' => $date,
                'class' => $class,
                'dosen_nip' => $user['nip'],
            ]);
            return redirect()->route('berita_acara.select_class')
                ->with('error', 'No perwalian session found for this date and class, or it has not been presented yet.');
        }

        // Check if a Berita Acara already exists
        $existingBeritaAcara = BeritaAcara::where('tanggal_perwalian', $date)
            ->where('kelas', $class)
            ->where('user_id', $user['user_id'])
            ->first();

        if ($existingBeritaAcara) {
            Log::warning('Berita Acara already exists for this Perwalian:', [
                'date' => $date,
                'class' => $class,
                'user_id' => $user['user_id'],
            ]);
            return redirect()->route('berita_acara.select_class')
                ->with('error', 'A Berita Acara has already been created for this perwalian session.');
        }

        // Fetch Dosen_Wali record to determine angkatan for display
        $dosenWali = DB::table('dosen_wali')->where('username', $user['username'])->first();
        $angkatan = $perwalian->angkatan; // Default to perwalian angkatan
        if ($dosenWali) {
            $kelasList = array_map('trim', explode(',', $dosenWali->kelas));
            $angkatanList = array_map('trim', explode(',', $dosenWali->angkatan));
            $kelasAngkatanMap = [];
            foreach ($kelasList as $index => $kelas) {
                $angkatanValue = isset($angkatanList[$index]) ? $angkatanList[$index] : end($angkatanList);
                $kelasAngkatanMap[$kelas] = $angkatanValue;
            }
            $angkatan = $kelasAngkatanMap[$class] ?? $perwalian->angkatan;
        }

        // Fetch Absensi records to display in the form
        $absensiRecords = Absensi::where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->where('kelas', $class)
            ->get();

        Log::info('Absensi records fetched for Berita Acara form:', [
            'perwalian_id' => $perwalian->ID_Perwalian,
            'count' => $absensiRecords->count(),
        ]);

        return view('perwalian.berita_acara', [
            'selectedClass' => $class,
            'selectedDate' => $date,
            'selectedAngkatan' => $angkatan, // Pass the determined angkatan for display
            'absensiRecords' => $absensiRecords,
            'perwalian' => $perwalian,
        ]);
    }

    public function store(Request $request, $date, $class)
{
    $user = session('user');
    if (!$user) {
        Log::error('No user authenticated in BeritaAcaraController@store');
        return response()->json(['success' => false, 'message' => 'Anda harus login untuk membuat berita acara.'], 401);
    }

    // Validate the request (keep angkatan for form submission, but it won't be used for storing)
    $request->validate([
        'kelas' => 'required|string|max:255',
        'angkatan' => 'required|integer', // Keep for form validation
        'tanggal_perwalian' => 'required|date|date_format:Y-m-d',
        'perihal_perwalian' => 'required|string|max:255',
        'agenda' => 'required|string',
        'hari_tanggal' => 'required|date|date_format:Y-m-d',
        'perihal2' => 'nullable|string|max:255',
        'catatan' => 'nullable|string',
        'tanggal_ttd' => 'required|date|date_format:Y-m-d',
        'dosen_wali_ttd' => 'required|string|max:255',
    ]);

    try {
        // Fetch Perwalian with Status "Presented"
        $perwalian = Perwalian::where('Tanggal', $date)
            ->where('kelas', $class)
            ->where('ID_Dosen_Wali', $user['nip'])
            ->where('Status', 'Presented') // Only allow "Presented" Perwalian
            ->first();

        if (!$perwalian) {
            Log::error('Perwalian not found or not in Presented status in BeritaAcaraController@store:', [
                'date' => $date,
                'class' => $class,
                'dosen_nip' => $user['nip'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'No perwalian session found for this date and class, or it has not been presented yet.',
            ], 400);
        }

        // Check if a Berita Acara already exists
        $existingBeritaAcara = BeritaAcara::where('tanggal_perwalian', $date)
            ->where('kelas', $class)
            ->where('user_id', $user['user_id'])
            ->first();

        if ($existingBeritaAcara) {
            Log::warning('Berita Acara already exists for this Perwalian:', [
                'date' => $date,
                'class' => $class,
                'user_id' => $user['user_id'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'A Berita Acara has already been created for this perwalian session.',
            ], 400);
        }

        // Fetch Dosen_Wali record to determine the correct angkatan for storing
        $dosenWali = DB::table('dosen_wali')->where('username', $user['username'])->first();
        if (!$dosenWali) {
            Log::error('Dosen Wali record not found for user in BeritaAcaraController@store:', [
                'username' => $user['username'],
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Dosen Wali record not found for this lecturer.',
            ], 404);
        }

        // Parse kelas and angkatan from dosen_wali
        $kelasList = array_map('trim', explode(',', $dosenWali->kelas));
        $angkatanList = array_map('trim', explode(',', $dosenWali->angkatan));

        // Create a mapping of kelas to angkatan
        $kelasAngkatanMap = [];
        foreach ($kelasList as $index => $kelas) {
            $angkatan = isset($angkatanList[$index]) ? $angkatanList[$index] : end($angkatanList);
            $kelasAngkatanMap[$kelas] = $angkatan;
        }

        // Determine the angkatan for the current kelas
        $angkatan = $kelasAngkatanMap[$class] ?? null;
        if (!$angkatan) {
            Log::error('Angkatan not found for kelas in BeritaAcaraController@store:', [
                'kelas' => $class,
                'kelas_list' => $kelasList,
                'angkatan_list' => $angkatanList,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Angkatan not found for the specified kelas.',
            ], 400);
        }

        // Determine keterangan based on tanggal_perwalian
        $tanggalPerwalian = Carbon::parse($request->tanggal_perwalian);
        $month = (int) $tanggalPerwalian->month;
        $day = (int) $tanggalPerwalian->day;
        Log::info('Extracted month and day from tanggal_perwalian', [
            'tanggal_perwalian' => $request->tanggal_perwalian,
            'month' => $month,
            'day' => $day,
        ]);

        $category = $this->determineCategory($month, $day);
        Log::info('Category determined from tanggal_perwalian', [
            'tanggal_perwalian' => $request->tanggal_perwalian,
            'month' => $month,
            'day' => $day,
            'category' => $category,
        ]);

        // Map category to keterangan (human-readable form)
        $categoryToKeteranganMap = [
            'semester_baru' => 'Semester Baru',
            'sebelum_uts' => 'Sebelum UTS',
            'sebelum_uas' => 'Sebelum UAS',
        ];
        $keterangan = $categoryToKeteranganMap[$category] ?? 'Semester Baru'; // Default to 'Semester Baru' if mapping fails
        Log::info('Keterangan mapped from category', [
            'category' => $category,
            'keterangan' => $keterangan,
        ]);

        // Create the Berita Acara using the determined angkatan and keterangan
        $beritaAcara = BeritaAcara::create([
            'user_id' => $user['user_id'],
            'kelas' => $request->kelas,
            'angkatan' => $angkatan, // Use the determined angkatan from dosen_wali
            'dosen_wali' => $user['username'],
            'tanggal_perwalian' => $request->tanggal_perwalian,
            'perihal_perwalian' => $request->perihal_perwalian,
            'agenda_perwalian' => $request->agenda,
            'hari_tanggal_feedback' => $request->hari_tanggal,
            'perihal_feedback' => $request->perihal2 ?? 'Tidak ada',
            'catatan_feedback' => $request->catatan,
            'tanggal_ttd' => $request->tanggal_ttd,
            'dosen_wali_ttd' => $request->dosen_wali_ttd,
            'keterangan' => $keterangan, // Add keterangan field
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update Perwalian status to "Completed"
        $perwalian->update(['Status' => 'Completed']);
        Log::info('Perwalian status updated to Completed after creating Berita Acara:', [
            'perwalian_id' => $perwalian->ID_Perwalian,
            'kelas' => $class,
            'date' => $date,
        ]);

        Log::info('Berita Acara created successfully:', [
            'berita_acara_id' => $beritaAcara->id,
            'date' => $date,
            'class' => $class,
            'angkatan' => $angkatan,
            'keterangan' => $keterangan,
            'user_id' => $user['user_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berita Acara created successfully. The perwalian session is now marked as completed.',
            'kelas' => $request->kelas,
            'angkatan' => $request->angkatan, // Return the angkatan from the request for display
            'tanggal_perwalian' => $request->tanggal_perwalian,
        ]);
    } catch (\Exception $e) {
        Log::error('Failed to create Berita Acara in BeritaAcaraController@store:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to create Berita Acara: ' . $e->getMessage(),
        ], 500);
    }
}

    public function success($kelas, $tanggal_perwalian)
    {
        $user = session('user');
        if (!$user) {
            Log::error('No user authenticated in BeritaAcaraController@success');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        return view('perwalian.berita_acara_success', [
            'kelas' => $kelas,
            'tanggal_perwalian' => $tanggal_perwalian,
        ]);
    }

    private function determineCategory($month, $day)
    {
        // Semester Genap: (months 1 to 5)
        if ($month == 1) {
            return 'semester_baru';
        }
        if ($month == 2) {
            if ($day == 1) {
                return 'semester_baru';
            }
            if ($day >= 2 && $day <= 29) {
                return 'sebelum_uts';
            }
        }
        if ($month == 3) {
            if ($day <= 10) {
                return 'sebelum_uts';
            }
            if ($day >= 11 && $day <= 31) {
                return 'sebelum_uas';
            }
        }
        if ($month == 4 || $month == 5) {
            if ($month == 5 && $day > 19) {
                return 'semester_baru';
            }
            return 'sebelum_uas';
        }
        // Semester Ganjil: (months 8 to 12)
        if ($month == 8) {
            return 'semester_baru';
        }
        if ($month == 9) {
            if ($day == 1) {
                return 'semester_baru';
            }
            if ($day >= 2 && $day <= 30) {
                return 'sebelum_uts';
            }
        }
        if ($month == 10) {
            if ($day <= 14) {
                return 'sebelum_uts';
            }
            if ($day >= 15 && $day <= 31) {
                return 'sebelum_uas';
            }
        }
        if ($month == 11) {
            return 'sebelum_uas';
        }
        if ($month == 12) {
            if ($day <= 11) {
                return 'sebelum_uas';
            }
        }
        return 'semester_baru';
    }
}