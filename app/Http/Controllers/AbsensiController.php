<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\StudentSyncService;
use App\Models\Absensi;
use App\Models\Perwalian;
use App\Models\Mahasiswa;

class AbsensiController extends Controller
{
    protected $studentSyncService;

    public function __construct(StudentSyncService $studentSyncService)
    {
        $this->middleware('auth');
        $this->middleware('role:dosen');
        $this->studentSyncService = $studentSyncService;
    }

    public function index()
    {
        $classes = [];
        $dosen = session('user');

        // Sync students from the API
        $year = now()->year;
        $this->studentSyncService->syncStudents($dosen['pegawai_id'], $year - 5, 2);

        try {
            $perwalianRecords = Perwalian::where('Status', 'Scheduled')->get();
            
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
            Log::error('Exception occurred in AbsensiController@index:', ['message' => $e->getMessage()]);
        }

        return view('perwalian.absensi_mahasiswa', compact('classes'));
    }

    public function show(Request $request, $date, $class)
    {
        $studentData = [];
        $students = [];
        $dosen = session('user');
        
        $perwalian = Perwalian::where('Tanggal', $date)
            ->where('kelas', $class)
            ->first();

        if (!$perwalian) {
            Log::error('Perwalian not found for date and class:', ['date' => $date, 'class' => $class]);
            return redirect()->route('absensi')
                ->with('error', 'No perwalian session found for this date and class.');
        }

        // Fetch students from the database (already synced in the index method)
        $students = Mahasiswa::where('kelas', $class)
            ->where('ID_Dosen', $dosen['pegawai_id'])
            ->get()
            ->map(function ($student) {
                return [
                    'nim' => $student->nim,
                    'nama' => $student->nama,
                ];
            })
            ->toArray();

        usort($students, function ($a, $b) {
            return strcmp($a['nim'], $b['nim']);
        });

        $studentData = array_column($students, null, 'nim');

        if (empty($students)) {
            Log::warning('No students found for class:', ['class' => $class, 'date' => $date]);
            $students = [];
        }

        // Attach students to the perwalian session by setting ID_Perwalian
        foreach ($students as $student) {
            $nim = $student['nim'];
            $username = 'ifs' . substr($nim, 3);

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
                Log::info('Updated student ID_Perwalian:', ['nim' => $mahasiswa->nim, 'perwalian_id' => $perwalian->ID_Perwalian]);
            }
        }

        $attendanceRecords = Absensi::where('ID_Perwalian', $perwalian->ID_Perwalian)
            ->where('kelas', $class)
            ->whereDate('created_at', $date)
            ->get()
            ->keyBy('nim');

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
            Log::error('Failed to save attendance data:', ['message' => $e->getMessage()]);
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
            Log::error('Exception occurred in AbsensiController@completed:', ['message' => $e->getMessage()]);
        }

        return view('perwalian.completed_absensi', compact('classes'));
    }
}