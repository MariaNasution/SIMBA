<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Dosen;
use App\Models\Dosen_Wali;
use App\Models\BeritaAcara;
use App\Models\Absensi;
use App\Exports\BeritaAcaraExport;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class KemahasiswaanPerwalianController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function jadwalPerwalian()
    {
        Log::info('jadwalPerwalian method called');
        return view('perwalian.perwalian_jadwal');
    }

    public function store(Request $request)
    {
        Log::info('Perwalian store request received', [
            'request' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        $validator = Validator::make($request->all(), [
            'jadwalMulai' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $startDate = Carbon::parse($value);
                    if ($startDate->isBefore(now())) {
                        $fail('Jadwal Mulai cannot be in the past.');
                    }
                },
            ],
            'jadwalSelesai' => [
                'required',
                'date',
                'after:jadwalMulai',
            ],
            'keterangan' => 'required|in:Semester Baru,Sebelum UTS,Sebelum UAS',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed in store method', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Validate keterangan against the date
        $startDate = Carbon::parse($request->jadwalMulai);
        $month = (int) $startDate->month;
        $day = (int) $startDate->day;
        $category = $this->determineCategory($month, $day);
        $categoryMap = [
            'Semester Baru' => 'semester_baru',
            'Sebelum UTS' => 'sebelum_uts',
            'Sebelum UAS' => 'sebelum_uas',
        ];
        $selectedCategory = $categoryMap[$request->keterangan] ?? null;

        if ($selectedCategory !== $category) {
            Log::error('Keterangan does not match the expected timeframe', [
                'keterangan' => $request->keterangan,
                'expected_category' => $category,
                'selected_category' => $selectedCategory,
                'date' => $startDate->toDateString(),
            ]);
            return response()->json([
                'success' => false,
                'errors' => [
                    'keterangan' => "The selected keterangan '{$request->keterangan}' is not valid for the date {$startDate->toDateString()}. Expected timeframe: " . array_search($category, $categoryMap, true),
                ],
            ], 422);
        }

        try {
            $user = session('user');
            Log::info('Session user data in store method', ['user' => $user]);

            if (!$user || !is_array($user) || !isset($user['role']) || $user['role'] !== 'kemahasiswaan') {
                Log::error('User is not kemahasiswaan in store method', ['user' => $user]);
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in as kemahasiswaan to schedule a Perwalian.'
                ], 401);
            }

            $usernames = DB::table('dosen_wali')->pluck('username')->toArray();
            Log::info('Fetched usernames from dosen_wali', ['usernames' => $usernames]);
            if (empty($usernames)) {
                Log::error('No usernames found in dosen_wali');
                return response()->json([
                    'success' => false,
                    'message' => 'No dosen wali usernames found to schedule Perwalian for.'
                ], 404);
            }

            $dosenList = Dosen::whereIn('username', $usernames)->get();
            Log::info('Fetched dosen records', [
                'count' => $dosenList->count(),
                'records' => $dosenList->toArray(),
            ]);
            if ($dosenList->isEmpty()) {
                Log::error('No matching dosen found for the dosen wali usernames');
                return response()->json([
                    'success' => false,
                    'message' => 'No matching dosen found for the dosen wali usernames.'
                ], 404);
            }

            $startDate = Carbon::parse($request->jadwalMulai);
            $existingPerwalian = Perwalian::where('role', 'dosen')
                ->where('Status', 'Scheduled')
                ->whereDate('Tanggal', $startDate->toDateString())
                ->first();

            if ($existingPerwalian) {
                Log::info('Existing Perwalian found', ['perwalian' => $existingPerwalian->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'A Perwalian session for dosen is already scheduled on this date.'
                ], 400);
            }

            $year = $startDate->year;
            $perwalianData = [
                'Tanggal' => $startDate,
                'Tanggal_Selesai' => Carbon::parse($request->jadwalSelesai),
                'Status' => 'Scheduled',
                'nama' => $user['username'],
                'kelas' => '',
                'angkatan' => $year,
                'role' => 'dosen',
                'keterangan' => $request->keterangan,
            ];

            $perwalian = DB::transaction(function () use ($perwalianData, $dosenList) {
                Log::info('Creating single Perwalian record', ['data' => $perwalianData]);
                $perwalian = Perwalian::create($perwalianData);
                Log::info('Made perwalian', ['perwalian' => $perwalian->toArray()]);

                $startDate = $perwalian->Tanggal;
                $endDate = $perwalian->Tanggal_Selesai ?? $startDate->copy()->addHours(2);
                $dayName = $startDate->translatedFormat('l');
                $formattedDate = $startDate->translatedFormat('j F Y');
                $startTime = $startDate->format('H:i');
                $endTime = $endDate->format('H:i');
                $formattedDateTime = "$dayName, $formattedDate at $startTime - $endTime";

                foreach ($dosenList as $dosen) {
                    $notificationMessage = "Perwalian scheduled for {$dosen->nama} - {$formattedDateTime}";
                    $this->notificationService->sendNotification(
                        $dosen,
                        $notificationMessage,
                        [
                            'type' => 'perwalian',
                            'id_perwalian' => $perwalian->ID_Perwalian,
                            'link' => '#',
                        ]
                    );
                    Log::info("Notification sent to Dosen {$dosen->username}: {$notificationMessage}");
                }

                return $perwalian;
            });

            Log::info('Transaction committed successfully');
            return response()->json([
                'success' => true,
                'message' => 'Perwalian scheduled successfully on ' . $startDate->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to schedule Perwalian', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule Perwalian: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function kelasPerwalian()
    {
        Log::info('kelasPerwalian method called');
        $perwalianList = Perwalian::where('role', 'mahasiswa')
            ->where('status', 'Scheduled')
            ->get();

        $dosenWaliUsernames = $perwalianList->pluck('ID_Dosen_Wali')->unique()->filter();

        $dosenWaliList = Dosen_Wali::whereIn('username', $dosenWaliUsernames)
            ->get()
            ->keyBy('username');

        $dosenList = Dosen::whereIn('nip', $dosenWaliUsernames)
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('dosen_wali')
                    ->whereColumn('dosen_wali.username', 'dosen.nip');
            })
            ->get()
            ->keyBy('nip');

        return view('perwalian.kemahasiswaan_perwalian_kelas', compact('perwalianList', 'dosenList', 'dosenWaliList'));
    }

    public function beritaAcaraPerwalian()
    {
        Log::info('beritaAcaraPerwalian method called');
        return view('perwalian.kemahasiswaan_perwalian_berita_acara');
    }

    public function searchBeritaAcara(Request $request)
    {
        try {
            Log::info('searchBeritaAcara request received', ['request' => $request->all()]);

            $prodiMap = [
                'S1Informatika' => 'IF',
                'S1TeknikRekayasaPerangkatLunak' => 'TRPL',
                'S1TeknikKomputer' => 'TK',
                'S1TeknikInformasi' => 'TI',
                'S1TeknikBioproses' => 'TB',
                'S1TeknikMetalurgi' => 'TM',
                'S1SistemInformasi' => 'SI',
                'S1TeknikElektro' => 'TE',
                'S1ManajemenRekayasa' => 'MR',
            ];

            $categoryMap = [
                'Semester Baru' => 'semester_baru',
                'Sebelum UTS' => 'sebelum_uts',
                'Sebelum UAS' => 'sebelum_uas',
            ];

            $validator = Validator::make($request->all(), [
                'prodi' => 'required|string|in:' . implode(',', array_keys($prodiMap)),
                'keterangan' => 'required|string|in:' . implode(',', array_keys($categoryMap)),
                'angkatan' => 'required|integer|min:2000|max:' . date('Y'),
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed in searchBeritaAcara', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $prodi = $request->input('prodi');
            $keterangan = $request->input('keterangan');
            $angkatan = $request->input('angkatan');
            $kelasPrefix = $prodiMap[$prodi] ?? null;

            if (!$kelasPrefix) {
                Log::error('Invalid prodi mapping', ['prodi' => $prodi]);
                return response()->json(['success' => false, 'message' => 'Invalid Prodi value.'], 400);
            }

            $kelasPatterns = ["%{$kelasPrefix}1", "%{$kelasPrefix}2"];
            $targetCategory = $categoryMap[$keterangan] ?? null;

            if (!$targetCategory) {
                Log::error('Invalid keterangan for category mapping', ['keterangan' => $keterangan]);
                return response()->json(['success' => false, 'message' => 'Invalid Keterangan value.'], 400);
            }

            // Fetch and filter BeritaAcara records
            $beritaAcaras = BeritaAcara::where('angkatan', $angkatan)
                ->where(function ($query) use ($kelasPatterns) {
                    $query->where('kelas', 'LIKE', $kelasPatterns[0])
                          ->orWhere('kelas', 'LIKE', $kelasPatterns[1]);
                })
                ->get()
                ->filter(function ($beritaAcara) use ($targetCategory) {
                    $month = (int) Carbon::parse($beritaAcara->tanggal_perwalian)->month;
                    $day = (int) Carbon::parse($beritaAcara->tanggal_perwalian)->day;
                    return $this->determineCategory($month, $day) === $targetCategory;
                })
                ->take(2);

            Log::info('Filtered BeritaAcara records', [
                'count' => $beritaAcaras->count(),
                'records' => $beritaAcaras->toArray(),
            ]);

            // Fetch related Perwalian records
            $perwalian = Perwalian::where('angkatan', $angkatan)
                ->where(function ($query) use ($kelasPatterns) {
                    $query->where('kelas', 'LIKE', $kelasPatterns[0])
                          ->orWhere('kelas', 'LIKE', $kelasPatterns[1]);
                })
                ->where('role', 'mahasiswa')
                ->where('status', 'Completed')
                ->where('keterangan', $keterangan)
                ->get();

            Log::info('Perwalian records fetched', [
                'count' => $perwalian->count(),
                'records' => $perwalian->toArray(),
            ]);

            $perwalianIds = $perwalian->pluck('ID_Perwalian')->all();
            $absensiRecords = Absensi::whereIn('ID_Perwalian', $perwalianIds)
                ->where(function ($query) use ($kelasPatterns) {
                    $query->where('kelas', 'LIKE', $kelasPatterns[0])
                          ->orWhere('kelas', 'LIKE', $kelasPatterns[1]);
                })
                ->get();

            Log::info('Absensi records fetched', [
                'count' => $absensiRecords->count(),
                'records' => $absensiRecords->toArray(),
            ]);

            $mahasiswaRecords = DB::table('mahasiswa')
                ->whereIn('ID_Perwalian', $perwalianIds)
                ->orderBy('nama')
                ->get()
                ->keyBy('nim');

            Log::info('Mahasiswa records fetched', [
                'count' => $mahasiswaRecords->count(),
                'records' => $mahasiswaRecords->toArray(),
            ]);

            $absensiWithNames = $absensiRecords->map(function ($absensi) use ($mahasiswaRecords, $beritaAcaras) {
                $absensiData = $absensi->toArray();
                $absensiData['nama'] = $mahasiswaRecords->get($absensi->nim)?->nama ?? 'Unknown';
                $berita = $beritaAcaras->firstWhere('kelas', $absensi->kelas);
                $absensiData['dosen_wali'] = $berita->dosen_wali ?? 'N/A';
                return (object) $absensiData;
            });

            Log::info('Absensi with names prepared', [
                'count' => $absensiWithNames->count(),
                'records' => $absensiWithNames->toArray(),
            ]);

            Log::info('Records fetched summary', [
                'prodi' => $prodi,
                'keterangan' => $keterangan,
                'angkatan' => $angkatan,
                'kelas_prefix' => $kelasPrefix,
                'perwalian_count' => $perwalian->count(),
                'absensi_count' => $absensiRecords->count(),
                'berita_acara_count' => $beritaAcaras->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'perwalian' => $perwalian->values(),
                    'absensi' => $absensiWithNames->values(),
                    'berita_acara' => $beritaAcaras->values(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchBeritaAcara', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to search Berita Acara: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportBeritaAcara(Request $request)
    {
        try {
            Log::info('exportBeritaAcara request received', ['request' => $request->all()]);

            $prodiMap = [
                'S1Informatika' => 'IF',
                'S1TeknikRekayasaPerangkatLunak' => 'TRPL',
                'S1TeknikKomputer' => 'TK',
                'S1TeknikInformasi' => 'TI',
                'S1TeknikBioproses' => 'TB',
                'S1TeknikMetalurgi' => 'TM',
                'S1SistemInformasi' => 'SI',
                'S1TeknikElektro' => 'TE',
                'S1ManajemenRekayasa' => 'MR',
            ];

            $categoryMap = [
                'Semester Baru' => 'semester_baru',
                'Sebelum UTS' => 'sebelum_uts',
                'Sebelum UAS' => 'sebelum_uas',
            ];

            $validator = Validator::make($request->all(), [
                'prodi' => 'required|string|in:' . implode(',', array_keys($prodiMap)),
                'keterangan' => 'required|string|in:' . implode(',', array_keys($categoryMap)),
                'angkatan' => 'required|integer|min:2000|max:' . date('Y'),
                'columns' => 'required|json',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed in exportBeritaAcara', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $prodi = $request->input('prodi');
            $keterangan = $request->input('keterangan');
            $angkatan = $request->input('angkatan');
            $columns = json_decode($request->input('columns'), true);

            if (empty($columns)) {
                Log::error('No columns selected for export', ['columns' => $columns]);
                return response()->json(['success' => false, 'message' => 'At least one column must be selected.'], 400);
            }

            $validColumns = ['nim', 'nama', 'status_kehadiran', 'keterangan_absensi', 'kelas', 'dosen_wali', 'prodi'];
            if (array_diff($columns, $validColumns)) {
                Log::error('Invalid columns selected', ['columns' => $columns]);
                return response()->json(['success' => false, 'message' => 'Invalid column selection.'], 400);
            }

            $kelasPrefix = $prodiMap[$prodi] ?? null;
            if (!$kelasPrefix) {
                Log::error('Invalid prodi mapping', ['prodi' => $prodi]);
                return response()->json(['success' => false, 'message' => 'Invalid Prodi value.'], 400);
            }

            $kelasPatterns = ["%{$kelasPrefix}1", "%{$kelasPrefix}2"];
            $targetCategory = $categoryMap[$keterangan] ?? null;

            if (!$targetCategory) {
                Log::error('Invalid keterangan for category mapping', ['keterangan' => $keterangan]);
                return response()->json(['success' => false, 'message' => 'Invalid Keterangan value.'], 400);
            }

            // Fetch BeritaAcara to get dosen_wali
            $beritaAcaras = BeritaAcara::where('angkatan', $angkatan)
                ->where(function ($query) use ($kelasPatterns) {
                    $query->where('kelas', 'LIKE', $kelasPatterns[0])
                          ->orWhere('kelas', 'LIKE', $kelasPatterns[1]);
                })
                ->get()
                ->filter(function ($beritaAcara) use ($targetCategory) {
                    $month = (int) Carbon::parse($beritaAcara->tanggal_perwalian)->month;
                    $day = (int) Carbon::parse($beritaAcara->tanggal_perwalian)->day;
                    return $this->determineCategory($month, $day) === $targetCategory;
                })
                ->take(2);

            // Fetch Perwal potwier�

            // Fetch related Perwalian records
            $perwalian = Perwalian::where('angkatan', $angkatan)
                ->where(function ($query) use ($kelasPatterns) {
                    $query->where('kelas', 'LIKE', $kelasPatterns[0])
                          ->orWhere('kelas', 'LIKE', $kelasPatterns[1]);
                })
                ->where('role', 'mahasiswa')
                ->where('status', 'Completed')
                ->where('keterangan', $keterangan)
                ->get();

            $perwalianIds = $perwalian->pluck('ID_Perwalian')->all();
            $absensiRecords = Absensi::whereIn('ID_Perwalian', $perwalianIds)
                ->where(function ($query) use ($kelasPatterns) {
                    $query->where('kelas', 'LIKE', $kelasPatterns[0])
                          ->orWhere('kelas', 'LIKE', $kelasPatterns[1]);
                })
                ->get();

            $mahasiswaRecords = DB::table('mahasiswa')
                ->whereIn('ID_Perwalian', $perwalianIds)
                ->orderBy('nama')
                ->get()
                ->keyBy('nim');

            $absensiWithNames = $absensiRecords->map(function ($absensi) use ($mahasiswaRecords, $beritaAcaras) {
                $absensiData = $absensi->toArray();
                $absensiData['nama'] = $mahasiswaRecords->get($absensi->nim)?->nama ?? 'Unknown';
                $berita = $beritaAcaras->firstWhere('kelas', $absensi->kelas);
                $absensiData['dosen_wali'] = $berita->dosen_wali ?? 'N/A';
                return (object) $absensiData;
            });

            if ($absensiWithNames->isEmpty()) {
                Log::info('No data available for export', ['filters' => $request->all()]);
                return response()->json([
                    'success' => false,
                    'message' => 'No data available for export.'
                ], 404);
            }

            $filename = 'Berita_Acara_' . $prodi . '_' . $angkatan . '_' . str_replace(' ', '_', $keterangan) . '.xlsx';
            return Excel::download(new BeritaAcaraExport($absensiWithNames, $columns), $filename);
        } catch (\Exception $e) {
            Log::error('Error in exportBeritaAcara', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to export Berita Acara: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function determineCategory($month, $day)
    {
        if ($month == 1) return 'semester_baru';
        if ($month == 2) {
            if ($day == 1) return 'semester_baru';
            if ($day >= 2 && $day <= 29) return 'sebelum_uts';
        }
        if ($month == 3) {
            if ($day <= 10) return 'sebelum_uts';
            if ($day >= 11 && $day <= 31) return 'sebelum_uas';
        }
        if ($month == 4 || $month == 5) {
            if ($month == 5 && $day > 19) return 'semester_baru';
            return 'sebelum_uas';
        }
        if ($month == 8) return 'semester_baru';
        if ($month == 9) {
            if ($day == 1) return 'semester_baru';
            if ($day >= 2 && $day <= 30) return 'sebelum_uts';
        }
        if ($month == 10) {
            if ($day <= 14) return 'sebelum_uts';
            if ($day >= 15 && $day <= 31) return 'sebelum_uas';
        }
        if ($month == 11) return 'sebelum_uas';
        if ($month == 12 && $day <= 11) return 'sebelum_uas';
        return 'semester_baru';
    }
}