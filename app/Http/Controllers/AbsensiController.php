<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\StudentSyncService;
use App\Models\Absensi;
use App\Models\Perwalian;
use App\Models\Mahasiswa;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    protected $studentSyncService;

    public function __construct(StudentSyncService $studentSyncService)
    {
        $this->studentSyncService = $studentSyncService;
    }

    public function index()
    {
        $classes = [];
        $dosen = session('user');

        if (!$dosen) {
            Log::error('No user authenticated in AbsensiController@index');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $dosenData = $dosen instanceof \Illuminate\Database\Eloquent\Model ? $dosen->toArray() : (array) $dosen;
        Log::info('Dosen data in AbsensiController@index', [
            'dosen' => $dosenData,
            'nip' => $dosen['nip'] ?? 'not set',
            'pegawai_id' => $dosen['pegawai_id'] ?? 'not set',
        ]);

        $year = now()->year;
        $syncYear = $year - 5;
        $semester = 2;

        // Fetch Perwalian records with Status "Scheduled"
        try {
            $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $dosen['nip'])
                ->where('Status', 'Scheduled') // Only show "Scheduled" Perwalian
                ->get();
            Log::info('Scheduled Perwalian records fetched', [
                'count' => $perwalianRecords->count(),
                'records' => $perwalianRecords->toArray(),
            ]);

            // Extract unique classes from Perwalian records
            $kelasList = $perwalianRecords->pluck('kelas')->unique()->toArray();
            Log::info('Unique classes from Perwalian', [
                'classes' => $kelasList,
            ]);

            // Sync students for each class
            foreach ($kelasList as $kelas) {
                Log::info('Calling syncStudents for class', [
                    'dosen_id' => $dosen['pegawai_id'],
                    'year' => $syncYear,
                    'semester' => $semester,
                    'kelas' => $kelas,
                ]);
                $this->studentSyncService->syncStudents($dosen['pegawai_id'], $syncYear, $semester, $kelas);
            }

            // Log the synced students
            $syncedStudents = Mahasiswa::where('ID_Dosen', $dosen['pegawai_id'])->get();
            Log::info('Students in mahasiswa table after sync', [
                'dosen_id' => $dosen['pegawai_id'],
                'student_count' => $syncedStudents->count(),
                'students' => $syncedStudents->toArray(),
            ]);

            // Prepare the classes for the view
            foreach ($perwalianRecords as $record) {
                $date = Carbon::parse($record->Tanggal);
                $classes[] = [
                    'year' => substr($record->kelas, 0, 2),
                    'date' => $date->format('Y-m-d'),
                    'class' => $record->kelas,
                    'formatted_date' => $date->translatedFormat('l, j F Y'),
                    'display' => $date->translatedFormat('l, j F Y') . " ({$record->kelas})",
                ];
            }

            usort($classes, function ($a, $b) {
                return strcmp($a['date'] . $a['class'], $b['date'] . $b['class']);
            });

            Log::info('Classes prepared for view', ['classes' => $classes]);
        } catch (\Exception $e) {
            Log::error('Exception occurred in AbsensiController@index:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return view('perwalian.absensi_mahasiswa', compact('classes'));
    }

    public function show(Request $request, $date, $class)
    {
        $studentData = [];
        $students = [];
        $dosen = session('user');

        if (!$dosen) {
            Log::error('No user authenticated in AbsensiController@show');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $dosenData = $dosen instanceof \Illuminate\Database\Eloquent\Model ? $dosen->toArray() : (array) $dosen;
        Log::info('AbsensiController@show called', [
            'date' => $date,
            'class' => $class,
            'dosen' => $dosenData,
            'nip' => $dosen['nip'] ?? 'not set',
            'pegawai_id' => $dosen['pegawai_id'] ?? 'not set',
        ]);

        // Fetch Perwalian with Status "Scheduled"
        $perwalian = Perwalian::where('Tanggal', $date)
            ->where('kelas', $class)
            ->where('ID_Dosen_Wali', $dosen['nip'])
            ->where('Status', 'Scheduled') // Only allow "Scheduled" Perwalian
            ->first();
        if (!$perwalian) {
            Log::error('Perwalian not found or not in Scheduled status:', [
                'date' => $date,
                'class' => $class,
                'dosen_nip' => $dosen['nip'],
            ]);
            return redirect()->route('absensi')
                ->with('error', 'No perwalian session found for this date and class, or it has already been presented or completed.');
        }

        Log::info('Perwalian record found', [
            'perwalian_id' => $perwalian->ID_Perwalian,
            'date' => $date,
            'class' => $class,
        ]);

        // Fetch students directly from the API
        $year = Carbon::parse($date)->year;
        $syncYear = $year - 5;
        $currentSem = 2; // Adjust based on your semester logic if needed
        $students = $this->studentSyncService->fetchStudents($dosen['pegawai_id'], $syncYear, $currentSem, $class);
        Log::info('Students fetched from API in AbsensiController@show', [
            'date' => $date,
            'class' => $class,
            'student_count' => count($students),
            'students' => $students,
        ]);

        // Map the API data to the required format
        $students = array_map(function ($student) {
            return [
                'nim' => $student['nim'],
                'nama' => $student['nama'],
            ];
        }, $students);

        Log::info('Students after mapping in AbsensiController@show', [
            'date' => $date,
            'class' => $class,
            'student_count' => count($students),
            'students' => $students,
        ]);

        usort($students, function ($a, $b) {
            return strcmp($a['nim'], $b['nim']);
        });

        $studentData = array_column($students, null, 'nim');

        if (empty($students)) {
            Log::warning('No students found for class:', [
                'class' => $class,
                'date' => $date,
            ]);
            $students = [];
        }

        // Sync students to the Mahasiswa table
        foreach ($students as $student) {
            $nim = $student['nim'];
            $username = 'ifs' . substr($nim, 3);

            Log::info('Processing student', [
                'nim' => $nim,
                'username' => $username,
                'class' => $class,
            ]);

            $mahasiswa = Mahasiswa::firstOrCreate(
                ['nim' => $nim],
                [
                    'username' => $username,
                    'nama' => $student['nama'],
                    'kelas' => $class,
                    'ID_Dosen' => $dosen['pegawai_id'],
                    'ID_Perwalian' => $perwalian->ID_Perwalian,
                ]
            );

            if ($mahasiswa->ID_Perwalian != $perwalian->ID_Perwalian) {
                $mahasiswa->update(['ID_Perwalian' => $perwalian->ID_Perwalian]);
                Log::info('Updated student ID_Perwalian:', [
                    'nim' => $mahasiswa->nim,
                    'perwalian_id' => $perwalian->ID_Perwalian,
                ]);
            }
        }

        $attendanceRecords = Absensi::where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->where('kelas', $class)
            ->whereDate('created_at', $date)
            ->get()
            ->keyBy('nim');

        Log::info('Attendance records fetched', [
            'perwalian_id' => $perwalian->ID_Perwalian,
            'class' => $class,
            'date' => $date,
            'record_count' => $attendanceRecords->count(),
        ]);

        foreach ($students as &$student) {
            $nim = $student['nim'] ?? null;
            if ($nim && isset($attendanceRecords[$nim])) {
                $student['status_kehadiran'] = $attendanceRecords[$nim]->status_kehadiran;
                $student['keterangan'] = $attendanceRecords[$nim]->keterangan ?? '';
            } else {
                $student['status_kehadiran'] = null;
                $student['keterangan'] = '';
            }
        }
        unset($student);

        $title = "Absensi Mahasiswa / $class Angkatan " . Carbon::parse($date)->year;

        return view('perwalian.perwalianKelas', compact('title', 'students', 'date', 'class', 'studentData', 'perwalian'));
    }

    public function store(Request $request, $date, $class)
    {
        $attendance = $request->input('attendance', []);
    
        if (empty($attendance)) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance data provided.',
            ], 400);
        }
    
        try {
            // Fetch the newest Perwalian record for the given date and class with Status "Scheduled"
            $dosen = session('user');
            $perwalian = Perwalian::where('Tanggal', $date)
                ->where('kelas', $class)
                ->where('ID_Dosen_Wali', $dosen['nip'])
                ->where('Status', 'Scheduled') // Only allow "Scheduled" Perwalian
                ->orderBy('created_at', 'desc') // Ensure the newest record is fetched
                ->first();
    
            if (!$perwalian) {
                Log::error('Perwalian not found or not in Scheduled status in AbsensiController@store:', [
                    'date' => $date,
                    'class' => $class,
                    'dosen_nip' => $dosen['nip'],
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No perwalian session found for this date and class, or it has already been presented or completed.',
                ], 404);
            }
    
            // Save attendance records
            $attendanceResults = [];
            foreach ($attendance as $nim => $data) {
                $status = $data['status'] ?? null;
                $keterangan = $data['keterangan'] ?? '';
    
                if (!$status || !in_array(strtolower($status), ['hadir', 'alpa', 'izin'])) {
                    Log::warning('Invalid or missing attendance status for student:', [
                        'nim' => $nim,
                        'status' => $status,
                    ]);
                    continue;
                }
    
                $status = strtolower($status) === 'alpa' ? 'alpa' : strtolower($status);
    
                $existingRecord = Absensi::where('ID_Perwalian', $perwalian->ID_Perwalian)
                    ->where('nim', $nim)
                    ->where('kelas', $class)
                    ->whereDate('created_at', $date)
                    ->first();
    
                if ($existingRecord) {
                    $existingRecord->update([
                        'status_kehadiran' => $status,
                        'keterangan' => $keterangan,
                        'updated_at' => now(),
                    ]);
                    Log::info('Updated existing attendance record:', [
                        'nim' => $nim,
                        'perwalian_id' => $perwalian->ID_Perwalian,
                        'status' => $status,
                    ]);
                } else {
                    Log::info('Created an absensi');
                    Absensi::create([
                        'ID_Perwalian' => $perwalian->ID_Perwalian,
                        'nim' => $nim,
                        'kelas' => $class,
                        'status_kehadiran' => $status,
                        'keterangan' => $keterangan,
                        'created_at' => $date,
                        'updated_at' => now(),
                    ]);
                    Log::info('Created new attendance record:', [
                        'nim' => $nim,
                        'perwalian_id' => $perwalian->ID_Perwalian,
                        'status' => $status,
                    ]);
                }
    
                // Collect attendance results to return
                $attendanceResults[$nim] = [
                    'status' => $status,
                    'keterangan' => $keterangan,
                ];
            }
    
            // Update Perwalian status to "Presented"
            $perwalian->update(['Status' => 'Presented']);
            Log::info('Perwalian status updated to Presented after saving attendance:', [
                'perwalian_id' => $perwalian->ID_Perwalian,
                'kelas' => $class,
                'date' => $date,
            ]);
    
            // Return JSON response with attendance results
            return response()->json([
                'success' => true,
                'message' => 'Attendance data saved successfully.',
                'attendance' => $attendanceResults,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save attendance data in AbsensiController@store:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function completed()
    {
        $classes = [];
        $dosen = session('user');

        if (!$dosen) {
            Log::error('No user authenticated in AbsensiController@completed');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        try {
            $perwalianRecords = Perwalian::where('ID_Dosen_Wali', $dosen['nip'])
                ->where('Status', 'Completed') // Only show "Completed" Perwalian
                ->get();
            
            foreach ($perwalianRecords as $record) {
                $date = Carbon::parse($record->Tanggal);
                $classes[] = [
                    'year' => substr($record->kelas, 0, 2),
                    'date' => $date->format('Y-m-d'),
                    'class' => $record->kelas,
                    'formatted_date' => $date->translatedFormat('l, j F Y'),
                    'display' => $date->translatedFormat('l, j F Y') . " ({$record->kelas})",
                ];
            }

            usort($classes, function ($a, $b) {
                return strcmp($a['date'] . $a['class'], $b['date'] . $b['class']);
            });

            Log::info('Completed Perwalian records prepared for view', ['classes' => $classes]);
        } catch (\Exception $e) {
            Log::error('Exception occurred in AbsensiController@completed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return view('perwalian.completed_absensi', compact('classes'));
    }
}