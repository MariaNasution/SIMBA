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
        return view('perwalian.perwalian_jadwal');
    }

    public function store(Request $request)
    {
        // Log the incoming request
        Log::info('Perwalian store request received', ['request' => $request->all()]);

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
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check if the user is kemahasiswaan
            $user = session('user');
            if (!$user || $user['role'] !== 'kemahasiswaan') {
                Log::error('User is not kemahasiswaan', ['user' => $user]);
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
                'nama' => $user['username'], // Use the kemahasiswaan username as the creator
                'kelas' => '',
                'angkatan' => $year,
                'role' => 'dosen', // Still associated with the 'dosen' role for scheduling purposes
                'keterangan' => $request->keterangan,
            ];

            $perwalian = DB::transaction(function () use ($perwalianData, $dosenList, $request) {
                Log::info('Creating single Perwalian record', ['data' => $perwalianData]);
                $perwalian = Perwalian::create($perwalianData);
                Log::info('Made perwalian', ['perwalian' => $perwalian->toArray()]);

                // Create notifications only for Dosen
                $notificationMessage = "Perwalian scheduled for {$request->jadwalMulai} to {$request->jadwalSelesai} (Keterangan: {$request->keterangan})";

                // Notify all Dosen
                foreach ($dosenList as $dosen) {
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
        // Fetch Perwalian records for mahasiswa with status Scheduled
        $perwalianList = Perwalian::where('role', 'mahasiswa')
            ->where('status', 'Scheduled')
            ->get();

        // Get the list of ID_Dosen_Wali from the Perwalian records
        $dosenWaliUsernames = $perwalianList->pluck('ID_Dosen_Wali')->unique()->filter();

        // Fetch only the Dosen_Wali records that are referenced by Perwalian
        $dosenWaliList = Dosen_Wali::whereIn('username', $dosenWaliUsernames)
            ->get()
            ->keyBy('username'); // Key the collection by username for easy lookup

        // Fetch Dosen records where there exists a Dosen_Wali with a matching username (dosen.nip = dosen_wali.username)
        $dosenList = Dosen::whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('dosen_wali')
                    ->whereColumn('dosen_wali.username', 'dosen.nip');
            })
            ->whereIn('nip', $dosenWaliUsernames) // Only fetch Dosen records that are referenced by Perwalian
            ->get()
            ->keyBy('nip'); // Key the collection by nip for easy lookup

        return view('perwalian.kemahasiswaan_perwalian_kelas', compact('perwalianList', 'dosenList', 'dosenWaliList'));
    }

    public function beritaAcaraPerwalian()
    {
        return view('perwalian.kemahasiswaan_perwalian_berita_acara');
    }

    public function searchBeritaAcara(Request $request)
    {
        $user = session('user');
        if (!$user || $user['role'] !== 'kemahasiswaan') {
            Log::error('User is not kemahasiswaan in KemahasiswaanPerwalianController@searchBeritaAcara', ['user' => $user]);
            return response()->json(['success' => false, 'message' => 'Anda harus login sebagai kemahasiswaan untuk mencari berita acara.'], 401);
        }

        try {
            // Get the filter inputs
            $prodi = $request->input('prodi');
            $keterangan = $request->input('keterangan');
            $angkatan = $request->input('angkatan');

            // Build the query
            $query = BeritaAcara::query();

            // Filter by Angkatan
            if ($angkatan && $angkatan !== 'Angkatan') {
                $query->where('angkatan', $angkatan);
            }

            // Filter by Prodi (based on kelas prefix, e.g., "12IF1" for Informatika)
            if ($prodi && $prodi !== 'Pilih Prodi') {
                $prodiPrefixes = [
                    'S1 Informatika' => ['IF'],
                    'S1 Sistem Informasi' => ['SI'],
                    'S1 Teknik Elektro' => ['EL'],
                    'D3 Teknologi Informasi' => ['TI'],
                    'D3 Teknologi Komputer' => ['TK'],
                    'D4 Teknologi Rekayasa Perangkat Lunak' => ['TRPL'],
                    'S1 Manajemen Rekayasa' => ['MR'],
                    'S1 Teknik Metalurgi' => ['MT'],
                    'S1 Bioproses' => ['BP'],
                ];

                if (isset($prodiPrefixes[$prodi])) {
                    $query->where(function ($q) use ($prodiPrefixes, $prodi) {
                        foreach ($prodiPrefixes[$prodi] as $prefix) {
                            $q->orWhere('kelas', 'LIKE', "%{$prefix}%");
                        }
                    });
                }
            }

            // Filter by Keterangan
            if ($keterangan && $keterangan !== 'Keterangan') {
                $this->applyKeteranganFilter($query, $keterangan);
            }

            // Log the query for debugging
            Log::info('Search Berita Acara Query', [
                'prodi' => $prodi,
                'keterangan' => $keterangan,
                'angkatan' => $angkatan,
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            // Fetch the filtered Berita Acara records with related Perwalian and Absensi
            $beritaAcaras = $query->with(['perwalian.absensi.mahasiswa'])->get();

            // Format the response
            $results = $beritaAcaras->map(function ($beritaAcara) {
                $absensiRecords = $beritaAcara->perwalian ? $beritaAcara->perwalian->absensi : collect();
                return [
                    'id' => $beritaAcara->id,
                    'kelas' => $beritaAcara->kelas,
                    'angkatan' => $beritaAcara->angkatan,
                    'dosen_wali' => $beritaAcara->dosen_wali,
                    'tanggal_perwalian' => $beritaAcara->tanggal_perwalian
                        ? Carbon::parse($beritaAcara->tanggal_perwalian)->translatedFormat('l, d F Y')
                        : 'N/A',
                    'perihal_perwalian' => $beritaAcara->perihal_perwalian ?? 'N/A',
                    'agenda_perwalian' => $beritaAcara->agenda_perwalian ?? 'N/A',
                    'hari_tanggal_feedback' => $beritaAcara->hari_tanggal_feedback
                        ? Carbon::parse($beritaAcara->hari_tanggal_feedback)->translatedFormat('l, d F Y')
                        : 'N/A',
                    'perihal_feedback' => $beritaAcara->perihal_feedback ?? 'N/A',
                    'catatan_feedback' => $beritaAcara->catatan_feedback ?? 'N/A',
                    'tanggal_ttd' => $beritaAcara->tanggal_ttd
                        ? Carbon::parse($beritaAcara->tanggal_ttd)->translatedFormat('d F Y')
                        : 'N/A',
                    'dosen_wali_ttd' => $beritaAcara->dosen_wali_ttd ?? 'N/A',
                    'absensi' => $absensiRecords->map(function ($absensi) {
                        return [
                            'nim' => $absensi->nim ?? 'N/A',
                            'nama' => $absensi->mahasiswa->nama ?? 'Unknown',
                            'status_kehadiran' => $absensi->status_kehadiran ?? 'N/A',
                            'keterangan' => $absensi->keterangan ?? '',
                        ];
                    })->toArray(),
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchBeritaAcara', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari Berita Acara: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function applyKeteranganFilter($query, $keterangan)
    {
        // Logic adapted from determineCategory
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