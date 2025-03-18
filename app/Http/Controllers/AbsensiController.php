<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Http;


class AbsensiController extends Controller
{
    public function index()
    {
        $apiToken = session('api_token');
        $nip = session('user')['username'] ?? null;
        $classes = [];
    
        if ($apiToken && $nip) {
            try {
                // Fetch the Dosen's assigned students/classes
                $dosenResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->get('https://cis-dev.del.ac.id/api/library-api/dosen', ['nip' => $nip]);
    
                if ($dosenResponse->successful()) {
                    $dosenData = $dosenResponse->json();
                    $dosenId = $dosenData['data']['dosen'][0]['pegawai_id'] ?? null;
    
                    if ($dosenId) {
                        $years = [2017, 2018, 2019, 2020];
                        foreach ($years as $index => $year) {
                            // Fetch students for this Dosen and year
                            $mahasiswaResponse = Http::withToken($apiToken)
                                ->withOptions(['verify' => false])
                                ->get('https://cis-dev.del.ac.id/api/library-api/get-all-students-by-dosen-wali', [
                                    'dosen_id' => $dosenId,
                                    'ta' => $year,
                                    'sem_ta' => 2,
                                ]);
    
                            if ($mahasiswaResponse->successful()) {
                                $responseData = $mahasiswaResponse->json();
                                if (isset($responseData['anak_wali']) && !empty($responseData['anak_wali'])) {
                                    // Generate dates starting from a base date (e.g., 2025-02-20) with 2-day increments
                                    $baseDate = \Carbon\Carbon::createFromDate(2025, 2, 20 + $index * 2);
                                    $classes[] = [
                                        'year' => $year,
                                        'date' => $baseDate->format('Y-m-d'),
                                        'class' => 'IF1',
                                        'formatted_date' => $baseDate->translatedFormat('l, j F Y'),
                                        'display' => $baseDate->translatedFormat('l, j F Y') . " (13 IF1 - $year)"
                                    ];
                                    $classes[] = [
                                        'year' => $year,
                                        'date' => $baseDate->addDay()->format('Y-m-d'),
                                        'class' => 'IF2',
                                        'formatted_date' => $baseDate->translatedFormat('l, j F Y'),
                                        'display' => $baseDate->translatedFormat('l, j F Y') . " (13 IF2 - $year)"
                                    ];
                                }
                            } else {
                                Log::warning("Failed to fetch students for Dosen {$nip} and year {$year}", ['status' => $mahasiswaResponse->status()]);
                            }
                        }
                    }
                } else {
                    Log::error('Failed to fetch Dosen data', ['status' => $dosenResponse->status()]);
                }
            } catch (\Exception $e) {
                Log::error('Exception in index:', ['message' => $e->getMessage()]);
            }
        }
        // Fallback to empty array if no data is fetched
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
