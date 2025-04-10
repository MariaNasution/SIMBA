<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Notifikasi;
use App\Models\Dosen;
use App\Models\Dosen_Wali;
use App\Models\BeritaAcara;
use App\Models\Absensi;
use App\Models\DosenWali;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class KemahasiswaanPerwalianController extends Controller
{
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
    
        // Validate the form inputs
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
    
        try {
            // Check if the user is kemahasiswaan
            $user = session('user');
            Log::info('Session user data in store method', ['user' => $user]);
    
            if (!$user || !is_array($user) || !isset($user['role']) || $user['role'] !== 'kemahasiswaan') {
                Log::error('User is not kemahasiswaan in store method', ['user' => $user]);
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in as kemahasiswaan to schedule a Perwalian.'
                ], 401);
            }
    
            // Fetch all usernames from the dosen_wali table
            $usernames = DB::table('dosen_wali')->pluck('username')->toArray();
            Log::info('Fetched usernames from dosen_wali', ['usernames' => $usernames]);
            if (empty($usernames)) {
                Log::error('No usernames found in dosen_wali');
                return response()->json([
                    'success' => false,
                    'message' => 'No dosen wali usernames found to schedule Perwalian for.'
                ], 404);
            }
    
            // Loop through usernames and fetch matching Dosen records
            $dosenList = [];
            foreach ($usernames as $username) {
                $dosen = Dosen::where('nip', $username)->first();
                if ($dosen) {
                    $dosenList[] = $dosen;
                    Log::info('Found Dosen for username', [
                        'username' => $username,
                        'dosen' => $dosen->toArray(),
                    ]);
                } else {
                    Log::warning('No Dosen found for username', ['username' => $username]);
                }
            }
    
            Log::info('Fetched dosen records', [
                'count' => count($dosenList),
                'records' => array_map(fn($dosen) => $dosen->toArray(), $dosenList),
            ]);
            if (empty($dosenList)) {
                Log::error('No matching dosen found for the dosen wali usernames');
                return response()->json([
                    'success' => false,
                    'message' => 'No matching dosen found for the dosen wali usernames.'
                ], 404);
            }
    
            // Check for existing scheduled Perwalian with role 'dosen' on the same date
            $startDate = Carbon::parse($request->jadwalMulai)->format('Y-m-d');
            $existingPerwalian = Perwalian::where('role', 'dosen')
                ->where('Status', 'Scheduled')
                ->whereDate('Tanggal', $startDate)
                ->first();
    
            if ($existingPerwalian) {
                Log::info('Existing Perwalian found', ['perwalian' => $existingPerwalian->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'A Perwalian session for dosen is already scheduled on this date.'
                ], 400);
            }
    
            // Create a single Perwalian record (not tied to a specific Dosen)
            $year = Carbon::parse($request->jadwalMulai)->year;
            $perwalianData = [
                'Tanggal' => Carbon::parse($request->jadwalMulai),
                'Tanggal_Selesai' => Carbon::parse($request->jadwalSelesai),
                'Status' => 'Scheduled',
                'nama' => $user['username'],
                'kelas' => '',
                'angkatan' => $year,
                'role' => 'dosen',
                'keterangan' => $request->keterangan,
            ];
    
            $perwalian = DB::transaction(function () use ($perwalianData, $dosenList, $request) {
                Log::info('Creating single Perwalian record', ['data' => $perwalianData]);
                $perwalian = Perwalian::create($perwalianData);
                Log::info('Made perwalian', ['perwalian' => $perwalian->toArray()]);
    
                // Format the date for the notification message
                $startDate = Carbon::parse($perwalian->Tanggal);
                $endDate = $perwalian->Tanggal_Selesai ? Carbon::parse($perwalian->Tanggal_Selesai) : $startDate->copy()->addHours(2);
                $dayName = $startDate->translatedFormat('l'); // e.g., "Jumat"
                $formattedDate = $startDate->translatedFormat('j F Y'); // e.g., "10 April 2025"
                $startTime = $startDate->format('H:i'); // e.g., "13:26"
                $endTime = $endDate->format('H:i'); // e.g., "16:26"
                $formattedDateTime = "$dayName, $formattedDate at $startTime - $endTime";
    
                // Create notifications only for Dosen
                foreach ($dosenList as $dosen) {
                    $notificationMessage = "Perwalian scheduled for {$dosen->nama} (Keterangan: {$perwalian->keterangan}) - {$formattedDateTime}";
                    $notificationData = [
                        'Pesan' => $notificationMessage,
                        'NIM' => null,
                        'Id_Perwalian' => $perwalian->ID_Perwalian,
                        'nama' => $dosen->nama,
                        'role' => 'dosen',
                    ];
                    Log::info('Creating notification for dosen', ['data' => $notificationData]);
                    $notification = Notifikasi::create($notificationData);
                    Log::info('Created notification for dosen', ['notification' => $notification->toArray()]);
                }
    
                return $perwalian;
            });
    
            Log::info('Transaction committed successfully');
    
            return response()->json([
                'success' => true,
                'message' => 'Perwalian scheduled successfully on ' . $request->jadwalMulai,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to schedule Perwalian: ' . $e->getMessage(), [
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
        // Fetch Perwalian records for mahasiswa with status Scheduled
        $perwalianList = Perwalian::where('role', 'mahasiswa')
            ->where('status', 'Scheduled')
            ->get();

        // Get the list of ID_Dosen_Wali from the Perwalian records
        $dosenWaliUsernames = $perwalianList->pluck('ID_Dosen_Wali')->unique()->filter();

        // Fetch only the Dosen_Wali records that are referenced by Perwalian
        $dosenWaliList = Dosen_Wali::whereIn('username', $dosenWaliUsernames)
            ->get()
            ->keyBy('username');

        // Fetch Dosen records where there exists a Dosen_Wali with a matching username
        $dosenList = Dosen::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('dosen_wali')
                    ->whereColumn('dosen_wali.username', 'dosen.nip');
            })
            ->whereIn('nip', $dosenWaliUsernames)
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
            // Log the incoming request for debugging
            Log::info('searchBeritaAcara request received', [
                'request' => $request->all(),
            ]);
    
            // Validate the request inputs
            $validator = Validator::make($request->all(), [
                'prodi' => 'required|string|in:S1Informatika,S1TeknikRekayasaPerangkatLunak,S1TeknikKomputer,S1TeknikInformasi,S1TeknikBioproses,S1TeknikMetalurgi,S1SistemInformasi,S1TeknikElektro,S1ManajemenRekayasa',
                'keterangan' => 'required|string|in:Semester Baru,Sebelum UTS,Sebelum UAS',
                'angkatan' => 'required|integer|min:2000|max:' . date('Y'),
            ]);
    
            Log::info('Validator instance created', [
                'rules' => $validator->getRules(),
            ]);
    
            if ($validator->fails()) {
                Log::error('Validation failed in searchBeritaAcara', [
                    'errors' => $validator->errors(),
                ]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }
    
            $prodi = $request->input('prodi');
            Log::info('Prodi value extracted', ['prodi' => $prodi]);
    
            $keterangan = $request->input('keterangan');
            Log::info('Keterangan value extracted', ['keterangan' => $keterangan]);
    
            $angkatan = $request->input('angkatan');
            Log::info('Angkatan value extracted', ['angkatan' => $angkatan]);
    
            // Map Prodi to kelas suffix (e.g., S1Informatika -> IF)
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
    
            Log::info('Prodi map defined', ['prodiMap' => $prodiMap]);
    
            $kelasPrefix = $prodiMap[$prodi] ?? null;
            Log::info('Kelas prefix determined', ['kelasPrefix' => $kelasPrefix]);
    
            if (!$kelasPrefix) {
                Log::error('Invalid prodi mapping', ['prodi' => $prodi]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Prodi value.',
                ], 400);
            }
    
            // Fetch all BeritaAcara records matching angkatan and kelas
            $query = BeritaAcara::query()
                ->with(['absensi.mahasiswa']) // Eager-load absensi and its mahasiswa relationship
                ->where('angkatan', $angkatan);
    
            Log::info('Query instance created', [
                'query' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);
    
            // Filter by kelas (e.g., 12IF1, 12IF2)
            $query->where(function ($q) use ($kelasPrefix) {
                $q->where('kelas', 'LIKE', "%{$kelasPrefix}1")
                  ->orWhere('kelas', 'LIKE', "%{$kelasPrefix}2");
            });
    
            Log::info('Query updated with kelas filter', [
                'query' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);
    
            // Get all matching records (no date filtering yet)
            $allBeritaAcaras = $query->get();
    
            Log::info('All BeritaAcara records fetched', [
                'allBeritaAcaras_count' => $allBeritaAcaras->count(),
                'allBeritaAcaras' => $allBeritaAcaras->toArray(),
            ]);
    
            // Log the initial fetch
            Log::info('Fetched all BeritaAcara records before filtering', [
                'prodi' => $prodi,
                'keterangan' => $keterangan,
                'angkatan' => $angkatan,
                'kelas_prefix' => $kelasPrefix,
                'results_count' => $allBeritaAcaras->count(),
            ]);
    
            // Map keterangan to category (as used in determineCategory)
            $categoryMap = [
                'Semester Baru' => 'semester_baru',
                'Sebelum UTS' => 'sebelum_uts',
                'Sebelum UAS' => 'sebelum_uas',
            ];
    
            Log::info('Category map defined', ['categoryMap' => $categoryMap]);
    
            $targetCategory = $categoryMap[$keterangan] ?? null;
            Log::info('Target category determined', ['targetCategory' => $targetCategory]);
    
            if (!$targetCategory) {
                Log::error('Invalid keterangan for category mapping', ['keterangan' => $keterangan]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Keterangan value.',
                ], 400);
            }
    
            // Filter BeritaAcara records based on tanggal_perwalian using determineCategory
            $filteredBeritaAcaras = $allBeritaAcaras->filter(function ($beritaAcara) use ($targetCategory) {
                // Extract month and day from tanggal_perwalian (ignoring year)
                $month = (int) $beritaAcara->tanggal_perwalian->month;
                Log::info('Month extracted from tanggal_perwalian', [
                    'beritaAcara_id' => $beritaAcara->id,
                    'month' => $month,
                ]);
    
                $day = (int) $beritaAcara->tanggal_perwalian->day;
                Log::info('Day extracted from tanggal_perwalian', [
                    'beritaAcara_id' => $beritaAcara->id,
                    'day' => $day,
                ]);
    
                // Use determineCategory to classify the date
                $category = $this->determineCategory($month, $day);
                Log::info('Category determined for BeritaAcara', [
                    'beritaAcara_id' => $beritaAcara->id,
                    'month' => $month,
                    'day' => $day,
                    'category' => $category,
                ]);
    
                // Keep the record if its category matches the target category
                $matches = $category === $targetCategory;
                Log::info('Category match check', [
                    'beritaAcara_id' => $beritaAcara->id,
                    'category' => $category,
                    'targetCategory' => $targetCategory,
                    'matches' => $matches,
                ]);
    
                return $matches;
            })->take(2); // Limit to 2 results after filtering
    
            Log::info('Filtered BeritaAcara records collection', [
                'filteredBeritaAcaras_count' => $filteredBeritaAcaras->count(),
                'filteredBeritaAcaras' => $filteredBeritaAcaras->toArray(),
            ]);
    
            // Log the filtered results
            Log::info('Filtered BeritaAcara records', [
                'prodi' => $prodi,
                'keterangan' => $keterangan,
                'angkatan' => $angkatan,
                'kelas_prefix' => $kelasPrefix,
                'filtered_results_count' => $filteredBeritaAcaras->count(),
            ]);
    
            $responseData = $filteredBeritaAcaras->values();
            Log::info('Response data prepared', [
                'responseData_count' => $responseData->count(),
                'responseData' => $responseData->toArray(),
            ]);
    
            return response()->json([
                'success' => true,
                'data' => $responseData,
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

/**
 * Determine the category of a date based on month and day (ignoring year).
 *
 * @param int $month
 * @param int $day
 * @return string
 */
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