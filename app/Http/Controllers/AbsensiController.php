<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\StudentSyncService;
use App\Models\Absensi;
use App\Models\Perwalian;
use App\Models\Mahasiswa;

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

        // Fetch Perwalian records to get the list of classes
        try {
            $perwalianRecords = Perwalian::where('Status', 'Scheduled')->get();
            Log::info('Perwalian records fetched', [
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
                $date = \Carbon\Carbon::parse($record->Tanggal);
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

        $perwalian = Perwalian::where('Tanggal', $date)
            ->where('kelas', $class)
            ->first();
        if (!$perwalian) {
            Log::error('Perwalian not found for date and class:', [
                'date' => $date,
                'class' => $class,
            ]);
            return redirect()->route('absensi')
                ->with('error', 'No perwalian session found for this date and class.');
        }

        Log::info('Perwalian record found', [
            'perwalian_id' => $perwalian->ID_Perwalian,
            'date' => $date,
            'class' => $class,
        ]);

        // Fetch students directly from the API
        $year = \Carbon\Carbon::parse($date)->year;
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

        $title = "Absensi Mahasiswa / $class Angkatan " . \Carbon\Carbon::parse($date)->year;

        return view('perwalian.perwalianKelas', compact('title', 'students', 'date', 'class', 'studentData', 'perwalian'));
    }

    public function store(Request $request, $date, $class)
    {
        $attendance = $request->input('attendance', []);

        if (empty($attendance)) {
            return redirect()->route('absensi.show', ['date' => $date, 'class' => $class])
                ->with('error', 'No attendance data provided.');
        }

        try {
            $perwalian = Perwalian::where('Tanggal', $date)
                ->where('kelas', $class)
                ->first();

            if (!$perwalian) {
                return redirect()->route('absensi.show', ['date' => $date, 'class' => $class])
                    ->with('error', 'No perwalian session found for this date and class.');
            }

            foreach ($attendance as $nim => $data) {
                $status = $data['status'] ?? null;
                $keterangan = $data['keterangan'] ?? '';

                if (!$status || !in_array(strtolower($status), ['hadir', 'alpa', 'izin'])) {
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
                } else {
                    Absensi::create([
                        'ID_Perwalian' => $perwalian->ID_Perwalian,
                        'nim' => $nim,
                        'kelas' => $class,
                        'status_kehadiran' => $status,
                        'keterangan' => $keterangan,
                        'created_at' => $date,
                        'updated_at' => now(),
                    ]);
                }
            }

            $perwalian->update(['Status' => 'Completed']);
            Log::info('Perwalian status updated to Completed:', ['perwalian_id' => $perwalian->ID_Perwalian]);

            return redirect()->route('absensi')
                ->with('success', 'Attendance data saved successfully, and perwalian session marked as completed.');
        } catch (\Exception $e) {
            Log::error('Failed to save attendance data:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('absensi.show', ['date' => $date, 'class' => $class])
                ->with('error', 'Failed to save attendance data.');
        }
    }

    public function completed()
    {
        $classes = [];

        try {
            $perwalianRecords = Perwalian::where('Status', 'Completed')->get();
            
            foreach ($perwalianRecords as $record) {
                $date = \Carbon\Carbon::parse($record->Tanggal);
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
        } catch (\Exception $e) {
            Log::error('Exception occurred in AbsensiController@completed:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return view('perwalian.completed_absensi', compact('classes'));
    }
}