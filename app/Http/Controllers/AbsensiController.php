<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index()
    {
        $classes = [];

        try {
            // Fetch all perwalian records to get the classes and dates
            $perwalianRecords = DB::table('perwalian')->get();
            dd($perwalianRecords);
            foreach ($perwalianRecords as $record) {
                $date = \Carbon\Carbon::parse($record->Tanggal);
                $classes[] = [
                    'year' => substr($record->kelas, 0, 2), // Extract the year from the kelas (e.g., '12' from '12IF1')
                    'date' => $date->format('Y-m-d'),
                    'class' => $record->kelas, // Use the actual kelas from the perwalian table
                    'formatted_date' => $date->translatedFormat('l, j F Y'),
                    'display' => $date->translatedFormat('l, j F Y') . " ({$record->kelas})",
                ];
            }

            // Sort classes by date and kelas
            usort($classes, function ($a, $b) {
                return strcmp($a['date'] . $a['class'], $b['date'] . $b['class']);
            });
        } catch (\Exception $e) {
            Log::error('Exception occurred in AbsensiController@index:', ['message' => $e->getMessage()]);
        }

        if (empty($classes)) {
            $classes = [];
        }

        return view('perwalian.absensi_mahasiswa', compact('classes'));
    }

    public function show(Request $request, $date, $class)
    {
        // Get API token from session
        $apiToken = session('api_token');
        $studentData = [];
        $students = [];

        if ($apiToken) {
            try {
                // Extract the year from the date (assuming date format is Y-m-d)
                $year = \Carbon\Carbon::parse($date)->year;

                // Fetch all students for the given year using the 'nim' prefix
                $nimPrefix = substr($year, -2) . 'S'; // Example: '20S' for 2020

                $response = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get('https://cis-dev.del.ac.id/api/library-api/mahasiswa', [
                        'nim' => '11S', // Fetch all students with NIMs starting with the year prefix
                    ]);
                    
                if ($response->successful()) {
                    $studentData = $response->json()['data']['mahasiswa'] ?? [];

                    // Assign students to IF1 or IF2 based on whether $class contains 'IF1' or 'IF2'
                    $halfStudents = ceil(count($studentData) / 2); // Round up to split into two groups
                    if (stripos($class, 'IF1') !== false) {
                        $students = array_slice($studentData, 0, $halfStudents);
                    } elseif (stripos($class, 'IF2') !== false) {
                        $students = array_slice($studentData, $halfStudents);
                    }

                    // Sort the student data by 'nim'
                    usort($students, function ($a, $b) {
                        return strcmp($a['nim'], $b['nim']);
                    });

                    // Index by NIM for easier lookup in the view
                    $studentData = array_column($students, null, 'nim');
                } else {
                    Log::error('API request failed:', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Exception $e) {
                Log::error('Exception occurred:', ['message' => $e->getMessage()]);
            }
        }

        // Fallback if API fails or no token
        if (empty($students)) {
            $students = [];
        }

        // Fetch existing attendance data from the database
        $attendanceRecords = DB::table('absensi')
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
        unset($student); // Unset the reference to avoid issues
        $title = "Absensi Mahasiswa / $class Angkatan $year";
        $attendanceData = []; // This would include status for each student (e.g., present, absent, permission)

        return view('perwalian.perwalianKelas', compact('title', 'students', 'date', 'class', 'attendanceData', 'studentData'));
    }

    public function store(Request $request, $date, $class)
    {
        $attendance = $request->input('attendance', []);

        try {
            foreach ($attendance as $nim => $data) {
                $status = $data['status'] ?? null;
                $keterangan = $data['keterangan'] ?? '';

                if ($status && in_array(strtolower($status), ['hadir', 'alpha', 'izin'])) {
                    // Normalize status to match the database enum
                    $status = strtolower($status) === 'alpha' ? 'alpa' : strtolower($status);

                    // Check if a record already exists for this student, class, and date
                    $existingRecord = DB::table('absensi')
                        ->where('nim', $nim)
                        ->where('kelas', $class)
                        ->whereDate('created_at', $date)
                        ->first();

                    if ($existingRecord) {
                        // Update the existing record
                        DB::table('absensi')
                            ->where('nim', $nim)
                            ->where('kelas', $class)
                            ->whereDate('created_at', $date)
                            ->update([
                                'status_kehadiran' => $status,
                                'keterangan' => $keterangan,
                                'updated_at' => now(),
                            ]);
                    } else {
                        // Insert a new record
                        DB::table('absensi')->insert([
                            'nim' => $nim,
                            'kelas' => $class,
                            'status_kehadiran' => $status,
                            'keterangan' => $keterangan,
                            'created_at' => $date,
                            'updated_at' => now(),
                        ]);
                    }

                    // Update the mahasiswa table with the latest statusKehadiran
                    DB::table('mahasiswa')
                        ->where('nim', $nim)
                        ->update(['statusKehadiran' => $status]);
                }
            }

            return redirect()->route('absensi.show', ['date' => $date, 'class' => $class])
                ->with('success', 'Attendance data saved successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to save attendance data:', ['message' => $e->getMessage()]);
            return redirect()->route('absensi.show', ['date' => $date, 'class' => $class])
                ->with('error', 'Failed to save attendance data.');
        }
    }
}