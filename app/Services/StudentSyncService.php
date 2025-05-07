<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Mahasiswa;

class StudentSyncService
{
    protected $baseUrl;
    protected $apiToken;

    public function __construct()
    {
        $this->baseUrl = env('API_BASE_URL', 'https://cis-dev.del.ac.id');
        $this->apiToken = env('API_TOKEN', session('api_token'));
    }

    public function syncStudents($dosenId, $year, $semester, $kelas)
    {
        $students = $this->fetchStudents($dosenId, $year, $semester, $kelas);
        foreach ($students as $student) {
            Mahasiswa::updateOrCreate(
                ['nim' => $student['nim']],
                [
                    'username' => 'ifs' . substr($student['nim'], 3),
                    'nama' => $student['nama'],
                    'kelas' => $student['kelas'],
                    'ID_Dosen' => $dosenId,
                ]
            );
        }
    }

    public function fetchStudents($dosenId, $year, $semester, $kelas)
    {
        $apiToken = session('api_token');
        $query = [
            'pegawai_id' => $dosenId,
            'ta' => $year,
            'sem_ta' => $semester,
        ];

        Log::info('Fetching students from API', [
            'url' => "{$this->baseUrl}/api/library-api/get-all-students-by-dosen-wali",
            'query' => $query,
        ]);

        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->get("{$this->baseUrl}/api/library-api/get-all-students-by-dosen-wali", $query);

                // dd($response);
            if ($response->successful()) {
                $responseData = $response->json();
                $daftarKelas = $responseData['daftar_kelas'] ?? [];

                // Flatten the student list by adding the kelas to each student
                $students = [];
                foreach ($daftarKelas as $classData) {
                    $classKel = $classData['kelas'] ?? null; // Use a different variable name

                    $anakWali = $classData['anak_wali'] ?? [];

                    foreach ($anakWali as $student) {
                        $student['kelas'] = $classKel; // Use the loop variable
                        $students[] = $student;
                    }
                }

                Log::info('Students fetched from API', [
                    'student_count' => count($students),
                    'students' => $students,
                ]);

                // Filter by kelas if provided
                if ($kelas) {
                    $students = array_filter($students, function ($student) use ($kelas) {
                        return $student['kelas'] === $kelas;
                    });
                    Log::info('Students filtered by kelas', [
                        'kelas' => $kelas,
                        'filtered_count' => count($students),
                    ]);
                }

                return $students;
            } else {
                Log::error('Failed to fetch students from API', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Exception while fetching students from API', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [];
        }
    }

}