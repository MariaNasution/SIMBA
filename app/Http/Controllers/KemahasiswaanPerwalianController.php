<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Notifikasi;
use App\Models\Dosen;
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
        return view('perwalian.perwalian_kelas');
    }

    public function beritaAcaraPerwalian()
    {
        return view('perwalian.perwalian_berita_acara');
    }
}