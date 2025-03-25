<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Absensi;
use App\Models\Perwalian;
use App\Models\Mahasiswa;

class AbsensiController extends Controller
{
    public function index()
    {
        $classes = [];

        try {
            // Only fetch perwalian records with Status = 'Scheduled'
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
        $apiToken = session('api_token');
        $studentData = [];
        $students = [];
        $dosen = session('user');
        
        // Find the Perwalian record for this date and class
        $perwalian = Perwalian::where('Tanggal', $date)
            ->where('kelas', $class)
            ->first();

        if (!$perwalian) {
            Log::error('Perwalian not found for date and class:', ['date' => $date, 'class' => $class]);
            return redirect()->route('absensi')
                ->with('error', 'No perwalian session found for this date and class.');
        }

        if ($apiToken) {
            try {
                $year = \Carbon\Carbon::parse($date)->year;
                $nimPrefix = substr($year, -2) . 'S';

                $response = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get("https://cis-dev.del.ac.id/api/library-api/get-all-students-by-dosen-wali", [
                        'dosen_id' => $dosen['pegawai_id'],
                        'ta' => $year - 5,
                        'sem_ta' => 2,
                    ]);
                
                if ($response->successful()) {
                    $classes = $response->json()['daftar_kelas'] ?? [];
                    
                    foreach ($classes as $Studentclass) {
                        if ($Studentclass['kelas'] === $class) {
                            $students = $Studentclass['anak_wali'] ?? [];
                            break;
                        }
                    }
                    
                    usort($students, function ($a, $b) {
                        return strcmp($a['nim'], $b['nim']);
                    });

                    $studentData = array_column($students, null, 'nim');
                } else {
                    Log::error('API request failed:', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Exception $e) {
                Log::error('Exception occurred in API call:', ['message' => $e->getMessage()]);
            }
        }

        if (empty($students)) {
            Log::warning('No students found for class:', ['class' => $class, 'date' => $date]);
            $students = [];
        }

        // Attach students to the perwalian session by setting ID_Perwalian
        foreach ($students as $student) {
            if (empty($student['nim'])) {
                Log::warning('Student NIM is empty:', ['student' => $student]);
                continue;
            }

            // Transform the username to match the database format (e.g., 11S19036 -> ifs19036)
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

            // Update the ID_Perwalian if it's not already set
            if ($mahasiswa->ID_Perwalian != $perwalian->ID_Perwalian) {
                $mahasiswa->update(['ID_Perwalian' => $perwalian->ID_Perwalian]);
                Log::info('Updated student ID_Perwalian:', ['nim' => $mahasiswa->nim, 'perwalian_id' => $perwalian->ID_Perwalian]);
            }
        }

        // Fetch attendance records for this perwalian session
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

        $title = "Absensi Mahasiswa / $class Angkatan $year";

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
            // Find the Perwalian record for this date and class
            $perwalian = Perwalian::where('Tanggal', $date)
                ->where('kelas', $class)
                ->first();

            if (!$perwalian) {
                return redirect()->route('absensi.show', ['date' => $date, 'class' => $class])
                    ->with('error', 'No perwalian session found for this date and class.');
            }

            // Save attendance data
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

            // Update the Perwalian status to 'Completed'
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
}