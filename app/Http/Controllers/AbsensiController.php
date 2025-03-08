<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;


class AbsensiController extends Controller
{
    public function index()
    {

        return view('perwalian.absensi_mahasiswa'); $classes = [
            [
                'date' => '2025-02-20',
                'class' => 'IF1',
                'formatted_date' => 'Senin, 20 Februari 2025',
                'display' => 'Senin, 20 Februari 2025 (13 IF1)'
            ],
            [
                'date' => '2025-02-21',
                'class' => 'IF2',
                'formatted_date' => 'Selasa, 21 Februari 2025',
                'display' => 'Selasa, 21 Februari 2025 (13 IF2)'
            ],
        ];
    
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
                // Fetch all students with base keyword '11S20'
                $response = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get('https://cis-dev.del.ac.id/api/library-api/mahasiswa', [
                        'nim' => '11S20', // Fetch all students with NIMs starting with 11S20
                    ]);

                if ($response->successful()) {
                    $studentData = $response->json()['data']['mahasiswa'] ?? [];

                    // Sort the student data by 'nim'
                    usort($studentData, function ($a, $b) {
                        return strcmp($a['nim'], $b['nim']);
                    });


                    // Calculate the number of students and split into IF1 and IF2
                    $totalStudents = count($studentData);
                    $halfStudents = ceil($totalStudents / 2); // Round up to split into two groups

                    // Assign students to IF1 and IF2 based on $class
                    if ($class === 'IF1') {
                        $students = array_slice($studentData, 0, $halfStudents);
                    } elseif ($class === 'IF2') {
                        $students = array_slice($studentData, $halfStudents);
                    }

                    // Index by NIM for easier lookup in the view
                    $studentData = array_column($studentData, null, 'nim');
                } else {
                    Log::error('API request failed:', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Exception $e) {
                Log::error('Exception occurred:', ['message' => $e->getMessage()]);
            }
        }

        // Fallback if API fails or no token
        if (empty($students)) {
            $students = []; // Empty array as fallback
        }

        $title = "Absensi Mahasiswa / IF {$class} Angkatan 2022";
        $attendanceData = []; // This would include status for each student (e.g., present, absent, permission)

        return view('perwalian.perwalianKelas', compact('title', 'students', 'date', 'class', 'attendanceData', 'studentData'));
    }
}
