<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Notifikasi;
use App\Models\Dosen;
use App\Models\Dosen_Wali;
use App\Models\BeritaAcara;
use App\Models\Absensi;
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

        // Map Prodi to kelas prefix
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

        $kelasPrefix = $prodiMap[$prodi] ?? null;
        if (!$kelasPrefix) {
            Log::error('Invalid prodi mapping', ['prodi' => $prodi]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid Prodi value.',
            ], 400);
        }

        // Build the query for berita_acaras with absensi relationship
        $query = BeritaAcara::query()
            ->with('absensi') // Eager-load the absensi relationship
            ->where('angkatan', $angkatan);
            Log::error('Invalid queries', ['query' => $ $query]);


        // Filter by kelas (e.g., 12IF1, 12IF2)
        $query->where(function ($q) use ($kelasPrefix) {
            $q->where('kelas', 'LIKE', "%{$kelasPrefix}1")
              ->orWhere('kelas', 'LIKE', "%{$kelasPrefix}2");
        });

        // Apply date range filters based on keterangan using applyKeteranganFilter
        $this->applyKeteranganFilter($query, $keterangan);

        // Limit to 2 results
        $beritaAcaras = $query->take(2)->get();

        Log::info('searchBeritaAcara query executed', [
            'prodi' => $prodi,
            'keterangan' => $keterangan,
            'angkatan' => $angkatan,
            'kelas_prefix' => $kelasPrefix,
            'results_count' => $beritaAcaras->count(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $beritaAcaras,
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

    private function applyKeteranganFilter($query, $keterangan)
    {
        Log::info('Applying keterangan filter logic', ['keterangan' => $keterangan]);
        if ($keterangan === 'Semester Baru') {
            $query->where(function ($q) {
                $q->whereMonth('tanggal_perwalian', 1)
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 2)
                        ->whereDay('tanggal_perwalian', 1);
                })
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 5)
                        ->whereDay('tanggal_perwalian', '>', 19);
                })
                ->orWhereMonth('tanggal_perwalian', 8)
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 9)
                        ->whereDay('tanggal_perwalian', 1);
                });
            });
        } elseif ($keterangan === 'Sebelum UTS') {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 2)
                        ->whereBetween('DAY(tanggal_perwalian)', [2, 29]);
                })
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 3)
                        ->whereBetween('DAY(tanggal_perwalian)', [1, 10]);
                })
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 9)
                        ->whereBetween('DAY(tanggal_perwalian)', [2, 30]);
                })
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 10)
                        ->whereBetween('DAY(tanggal_perwalian)', [1, 14]);
                });
            });
        } elseif ($keterangan === 'Sebelum UAS') {
            $query->where(function ($q) {
                $q->where(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 3)
                        ->whereBetween('DAY(tanggal_perwalian)', [11, 31]);
                })
                ->orWhereMonth('tanggal_perwalian', 4)
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 5)
                        ->whereDay('tanggal_perwalian', '<=', 19);
                })
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 10)
                        ->whereBetween('DAY(tanggal_perwalian)', [15, 31]);
                })
                ->orWhereMonth('tanggal_perwalian', 11)
                ->orWhere(function ($q2) {
                    $q2->whereMonth('tanggal_perwalian', 12)
                        ->whereDay('tanggal_perwalian', '<=', 11);
                });
            });
        }
    }
}