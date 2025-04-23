<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Perwalian;
use App\Services\StudentSyncService;
use Illuminate\Support\Facades\Cache;

class DosenController extends Controller
{
    protected $studentSyncService;

    public function __construct(StudentSyncService $studentSyncService)
    {
        $this->studentSyncService = $studentSyncService;
    }

    public function beranda()
    {
        ini_set('max_execution_time', 120);

        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $apiToken = session('api_token');
        $nip = session('user')['username'];
        $baseUrl = 'https://cis-dev.del.ac.id';
        
        if (!$apiToken) {
            return back()->with('error', 'API token not found.');
        }

        try {
            // 1. Fetch and update dosen details
            $dosenData = $this->fetchDosenData($apiToken, $baseUrl, $nip);
            if (!$dosenData) {
                return back()->with('error', 'Failed to fetch lecturer data.');
            }
            
            $dosenId = $dosenData['pegawai_id'] ?? null;
            if (!$dosenId) {
                return back()->with('error', 'Dosen ID not found.');
            }

            // 2. Fetch Dosen_Wali record and validate
            $dosenWali = DB::table('dosen_wali')->where('username', $nip)->first();
            if (!$dosenWali) {
                return back()->with('error', 'Dosen Wali record not found for this lecturer.');
            }

            // 3. Parse kelas and angkatan from dosen_wali
            $kelasAngkatanMap = $this->parseKelasAngkatan($dosenWali);
            $kelasList = array_keys($kelasAngkatanMap);
            
            // 4. Process academic years in parallel
            $academicYears = [2017, 2018, 2019, 2020];
            $currentSem = 2;
            
            $studentsByYear = [];
            $prodisByYear = [];
            $semesterAveragesByYear = [];
            $angkatanByKelasAndYear = [];
            
            foreach ($academicYears as $year) {
                // Initialize arrays for this year
                $studentsByYear[$year] = [];
                $prodisByYear[$year] = [];
                $semesterAveragesByYear[$year] = [];
                $angkatanByKelasAndYear[$year] = [];
                
                // 5. Fetch students for this year
                $yearData = $this->processYearData(
                    $dosenId, 
                    $year, 
                    $currentSem, 
                    $kelasList, 
                    $kelasAngkatanMap, 
                    $apiToken, 
                    $baseUrl
                );
                
                // Merge the results
                $studentsByYear[$year] = $yearData['students'] ?? [];
                $prodisByYear[$year] = $yearData['prodis'] ?? [];
                $semesterAveragesByYear[$year] = $yearData['semesterAverages'] ?? [];
                $angkatanByKelasAndYear[$year] = $yearData['angkatanByKelas'] ?? [];
            }

            // 6. Check perwalian announcements
            $perwalianAnnouncement = $this->checkPerwalian($nip, $apiToken, $baseUrl);
            
            // 7. Return the view with all the data
            return view('beranda.homeDosen', compact(
                'studentsByYear', 
                'prodisByYear', 
                'angkatanByKelasAndYear', 
                'perwalianAnnouncement', 
                'semesterAveragesByYear'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error fetching data in beranda:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An error occurred while fetching data: ' . $e->getMessage());
        }
    }

/**
 * Fetch and update dosen data in the session
 */
private function fetchDosenData($apiToken, $baseUrl, $nip)
{
    $cacheKey = "dosen_data_{$nip}";
    
    // Try to get from cache first
    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }
    
    $dosenResponse = Http::withToken($apiToken)
        ->withOptions(['verify' => false])
        ->timeout(15)
        ->get("{$baseUrl}/api/library-api/dosen", ['nip' => $nip]);
    
    if (!$dosenResponse->successful()) {
        Log::error('Failed to fetch dosen data', [
            'status' => $dosenResponse->status(),
            'response' => $dosenResponse->body(),
        ]);
        return null;
    }

    $dosenData = $dosenResponse->json();
    $dosenSession = $dosenData['data']['dosen'][0];
    
    // Update session
    session()->forget('user');
    session(['user' => [
        "username" => $nip,
        "role" => 'dosen',
        "pegawai_id" => $dosenSession['pegawai_id'],
        "dosen_id" => $dosenSession['dosen_id'],
        "nip" => $dosenSession['nip'],
        "nama" => $dosenSession['nama'],
        "email" => $dosenSession['email'],
        "prodi_id" => $dosenSession['prodi_id'],
        "prodi" => $dosenSession['prodi'],
        "jabatan_akademik" => $dosenSession['jabatan_akademik'],
        "jabatan_akademik_desc" => $dosenSession['jabatan_akademik_desc'],
        "jenjang_pendidikan" => $dosenSession['jenjang_pendidikan'],
        "nidn" => $dosenSession['nidn'],
        "user_id" => $dosenSession['user_id'],
    ]]);
    
    // Cache the result
    Cache::put($cacheKey, $dosenSession, now()->addHour());
    
    return $dosenSession;
}

/**
 * Parse kelas and angkatan from dosen_wali record
 */
private function parseKelasAngkatan($dosenWali)
{
    $kelasList = array_map('trim', explode(',', $dosenWali->kelas));
    $angkatanList = array_map('trim', explode(',', $dosenWali->angkatan));

    // Create a mapping of kelas to angkatan
    $kelasAngkatanMap = [];
    foreach ($kelasList as $index => $kelas) {
        $angkatan = isset($angkatanList[$index]) ? $angkatanList[$index] : end($angkatanList);
        $kelasAngkatanMap[$kelas] = $angkatan;
    }
    
    return $kelasAngkatanMap;
}

/**
 * Process data for a specific academic year
 */
private function processYearData($dosenId, $year, $currentSem, $kelasList, $kelasAngkatanMap, $apiToken, $baseUrl)
{
    $cacheKey = "year_data_{$dosenId}_{$year}_{$currentSem}";
    
    // Try to get from cache first
    if (Cache::has($cacheKey)) {
        return Cache::get($cacheKey);
    }
    
    $students = $this->studentSyncService->fetchStudents($dosenId, $year, $currentSem, null);
    
    if (empty($students)) {
        Log::warning("No students fetched for year {$year}", [
            'dosen_id' => $dosenId,
            'semester' => $currentSem,
        ]);
        return [
            'students' => [],
            'prodis' => [],
            'semesterAverages' => [],
            'angkatanByKelas' => [],
        ];
    }

    $studentsByKelas = [];
    $angkatanByKelas = [];
    
    // Filter and group students by kelas
    foreach ($students as $student) {
        $kelas = $student['kelas'] ?? null;
        if (is_null($kelas) || empty($kelas) || !in_array($kelas, $kelasList)) {
            continue;
        }
        
        $studentsByKelas[$kelas][] = $student;
        $angkatanByKelas[$kelas] = $kelasAngkatanMap[$kelas] ?? $year;
    }
    
    // Process each class of students
    $result = [
        'students' => [],
        'prodis' => [],
        'semesterAverages' => [],
        'angkatanByKelas' => $angkatanByKelas,
    ];
    
    foreach ($studentsByKelas as $kelas => $classStudents) {
        // Batch fetch penilaian data for all students in this class
        $penilaianDataBatch = $this->batchFetchPenilaian($classStudents, $apiToken, $baseUrl, $year, $currentSem);
        
        // Process each student with their penilaian data
        $semesterTotals = [];
        $processedStudents = $this->processStudentsData($classStudents, $penilaianDataBatch, $kelas, $semesterTotals);
        
        $result['students'][$kelas] = $processedStudents;
        $result['prodis'][$kelas] = $this->kelasToProdi($kelas) ?? null;
        
        // Calculate semester averages
        $averages = [];
        foreach ($semesterTotals as $sem => $data) {
            if ($data['count'] > 0) {
                $averages[$sem] = number_format($data['total'] / $data['count'], 2);
            }
        }
        $result['semesterAverages'][$kelas] = $averages;
    }
    
    // Cache the result
    Cache::put($cacheKey, $result, now()->addHour());
    
    return $result;
}

/**
 * Process student data with penilaian information
 */
private function processStudentsData($students, $penilaianDataBatch, $kelas, &$semesterTotals)
{
    $processedStudents = [];
    
    foreach ($students as $student) {
        $nim = $student['nim'] ?? null;
        if (!$nim) continue;

        $penilaianData = $penilaianDataBatch[$nim] ?? [
            'IP' => '0.00',
            'IP Semester' => [],
            'status_krs' => 'Approved',
        ];

        $ipk = isset($penilaianData['IP']) && is_numeric($penilaianData['IP']) 
            ? number_format(floatval($penilaianData['IP']), 2)
            : null;

        $ipSemesterData = $penilaianData['IP Semester'] ?? [];
        $validIps = [];
        
        foreach ($ipSemesterData as $entry) {
            if (isset($entry['ip_semester']) && isset($entry['sem']) && 
                is_numeric($entry['ip_semester']) && 
                $entry['ip_semester'] !== "Belum di-generate") {
                $sem = $entry['sem'];
                $ip = floatval($entry['ip_semester']);
                $validIps[] = $entry;

                if (!isset($semesterTotals[$sem])) {
                    $semesterTotals[$sem] = ['total' => 0, 'count' => 0];
                }
                $semesterTotals[$sem]['total'] += $ip;
                $semesterTotals[$sem]['count'] += 1;
            }
        }

        $ips = null;
        $semester = null;
        
        if (!empty($validIps)) {
            usort($validIps, function ($a, $b) {
                return $b['sem'] - $a['sem'];
            });
            $ips = $validIps[0]['ip_semester'];
            $semester = $validIps[0]['sem'];
        } else {
            $semester = 1; // Default to first semester if no valid IPs
        }

        $statusKrs = $penilaianData['status_krs'] ?? null;
        
        $student['ipk'] = $ipk;
        $student['ips'] = $ips;
        $student['status_krs'] = $statusKrs;
        $student['semester'] = $semester;
        $student['kelas'] = $kelas;
        
        $processedStudents[] = $student;
    }
    
    return $processedStudents;
}
    public function showDetailedClass($year, $kelas)
    {
        ini_set('max_execution_time', 120);

        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $apiToken = session('api_token');
        $nip = session('user')['username'];
        $baseUrl = 'https://cis-dev.del.ac.id';
        $students = [];
        $currentSem = 2;

        if ($apiToken) {
            try {
                $dosenResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->timeout(15)
                    ->get("{$baseUrl}/api/library-api/dosen", ['nip' => $nip]);

                if (!$dosenResponse->successful()) {
                    Log::error('Failed to fetch dosen data', [
                        'status' => $dosenResponse->status(),
                        'response' => $dosenResponse->body(),
                    ]);
                    return back()->with('error', 'Failed to fetch lecturer data.');
                }

                $dosenData = $dosenResponse->json();
                // search by pegawai_id not dosen_id
                $dosenId = $dosenData['data']['dosen'][0]['pegawai_id'] ?? null;
                if (!$dosenId) {
                    return back()->with('error', 'Dosen ID not found.');
                }                

                $studentsInClass = $this->studentSyncService->fetchStudents($dosenId, $year, $currentSem, $kelas);

                if (empty($studentsInClass)) {
                    Log::warning("No students found for year {$year} and kelas {$kelas}", [
                        'dosen_id' => $dosenId,
                    ]);
                    return back()->with('error', 'No students found for this class.');
                }

                $penilaianDataBatch = $this->batchFetchPenilaian($studentsInClass, $apiToken, $baseUrl, $year, $currentSem);

                foreach ($studentsInClass as $student) {
                    if (!is_array($student)) {
                        Log::warning("Student data is not an array for class {$kelas}", ['student' => $student]);
                        continue;
                    }

                    $nim = $student['nim'] ?? null;
                    if (!$nim) {
                        Log::warning("Student NIM is missing for class {$kelas}", ['student' => $student]);
                        continue;
                    }

                    if (!isset($student['nama'])) {
                        Log::warning("Student name is missing for NIM {$nim}", ['student' => $student]);
                        continue;
                    }

                    $penilaianData = $penilaianDataBatch[$nim] ?? [
                        'IP' => '0.00',
                        'IP Semester' => [],
                        'status_krs' => 'Approved',
                    ];

                    $ipk = isset($penilaianData['IP']) && is_numeric($penilaianData['IP'])
                        ? number_format(floatval($penilaianData['IP']), 2)
                        : null;

                    $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                    if (!is_array($ipSemesterData)) {
                        Log::warning("IP Semester data is not an array for student {$nim}", ['ipSemesterData' => $ipSemesterData]);
                        $ipSemesterData = [];
                    }

                    $validIps = [];
                    foreach ($ipSemesterData as $entry) {
                        if (!is_array($entry)) {
                            Log::warning("IP Semester entry is not an array for student {$nim}", ['entry' => $entry]);
                            continue;
                        }
                        if (isset($entry['ip_semester']) && isset($entry['sem']) && 
                            is_numeric($entry['ip_semester']) && 
                            $entry['ip_semester'] !== "Belum di-generate") {
                            $validIps[] = $entry;
                        }
                    }

                    $ips = null;
                    if (!empty($validIps)) {
                        usort($validIps, function ($a, $b) {
                            return $b['sem'] - $a['sem'];
                        });
                        $ips = $validIps[0]['ip_semester'];
                    }

                    $statusKrs = $penilaianData['status_krs'] ?? null;
                    $semester = !empty($validIps) ? $validIps[0]['sem'] : $currentSem;

                    $studentData = array_merge($student, [
                        'ipk' => $ipk,
                        'ips' => $ips,
                        'status_krs' => $statusKrs,
                        'semester' => $semester,
                        'kelas' => $kelas,
                    ]);

                    $students[] = $studentData;
                }

                $perwalianAnnouncement = $this->checkPerwalian($dosenId, $apiToken, $baseUrl);
            } catch (\Exception $e) {
                Log::error('Error fetching data in showDetailedClass:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return back()->with('error', 'An error occurred while fetching data: ' . $e->getMessage());
            }
        }

        return view('dosen.detailedClass', compact('students', 'year', 'kelas', 'perwalianAnnouncement'));
    }

    private function batchFetchPenilaian($students, $apiToken, $baseUrl, $year, $semester)
    {
        $penilaianDataBatch = [];
        $nims = array_filter(array_map(function ($student) {
            return $student['nim'] ?? null;
        }, $students));

        if (empty($nims)) {
            return $penilaianDataBatch;
        }

        $promises = [];
        foreach ($nims as $nim) {
            $cacheKey = "penilaian_{$nim}_" . ($year ?? 'no_year') . "_{$semester}";
            $cachedData = Cache::get($cacheKey);

            if ($cachedData) {
                $penilaianDataBatch[$nim] = $cachedData;
                continue;
            }

            $promises[$nim] = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->async()
                ->get("{$baseUrl}/api/library-api/get-penilaian", [
                    'nim' => $nim,
                ]);
        }
    
        foreach ($promises as $nim => $promise) {
            try {
                $response = $promise->wait();
                if ($response->successful()) {
                    $penilaianData = $response->json();
                    $penilaianDataBatch[$nim] = $penilaianData;
                    Cache::put("penilaian_{$nim}_" . ($year ?? 'no_year') . "_{$semester}", $penilaianData, now()->addHour());
                } else {
                    Log::warning("Failed to fetch penilaian data for student {$nim}", [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    $penilaianDataBatch[$nim] = [
                        'IP' => '0.00',
                        'IP Semester' => [],
                        'status_krs' => 'Approved',
                    ];
                }
            } catch (\Exception $e) {
                Log::error("Error fetching penilaian data for student {$nim}", [
                    'message' => $e->getMessage(),
                    'nim' => $nim,
                    'sem_ta' => $semester,
                ]);
                $penilaianDataBatch[$nim] = [
                    'IP' => '0.00',
                    'IP Semester' => [],
                    'status_krs' => 'Approved',
                ];
            }
        }
        return $penilaianDataBatch;
    }

    private function checkPerwalian($dosenId, $apiToken, $baseUrl)
    {
        $user = session('user');
        try {
            $perwalian = Perwalian::where('ID_Dosen_Wali', $user['nip'])
                ->where('Status', 'Scheduled')
                ->get();

            if ($perwalian->isNotEmpty()) {
                $announcements = [];
                foreach ($perwalian as $p) {
                    $date = \Carbon\Carbon::parse($p->Tanggal)->format('D, d/m/Y');
                    $announcements[] = "Jadwal Perwalian untuk kelas {$p->kelas}({$date})";
                }
                return implode("\n", $announcements);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error checking perwalian:', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function kelasToProdi($kelas)
    {
        $prodi = substr($kelas, 2);
        $prodi = preg_replace('/\d+$/', '', $prodi);

        $prodiMap = [
            'IF' => 'Informatika',
            'TRPL' => 'Teknik Rekayasa Perangkat Lunak',
            'TK' => 'Teknik Komputer',
            'TI' => 'Teknik Informasi',
            'TB' => 'Teknik Bioproses',
            'TM' => 'Teknik Metalurgi',
            'SI' => 'Sistem Informasi',
            'TE' => 'Teknik Elektro',
            'MR' => 'Manajemen Rekayasa',
        ];

        return $prodiMap[$prodi] ?? $prodi;
    }
}