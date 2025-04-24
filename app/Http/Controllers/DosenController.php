<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Perwalian;
use App\Services\StudentSyncService;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Promise\Utils;

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
        $nip = session('user')['nip']; // Use nip from session
        $baseUrl = 'https://cis-dev.del.ac.id';
        
        if (!$apiToken) {
            return back()->with('error', 'API token not found.');
        }

        try {
            $dosenData = $this->fetchDosenData($apiToken, $baseUrl, $nip);
            if (!$dosenData) {
                return back()->with('error', 'Failed to fetch lecturer data.');
            }
            
            $dosenId = $dosenData['pegawai_id'] ?? null;
            if (!$dosenId) {
                return back()->with('error', 'Dosen ID not found.');
            }

            $dosenWali = DB::table('dosen_wali')->where('username', $nip)->first();
            if (!$dosenWali) {
                return back()->with('error', 'Dosen Wali record not found for this lecturer.');
            }

            $kelasAngkatanMap = $this->parseKelasAngkatan($dosenWali);
            $kelasList = array_keys($kelasAngkatanMap);
            
            $academicYears = [2020];
            $currentSem = 2;
            
            $studentsByYear = [];
            $prodisByYear = [];
            $semesterAveragesByYear = [];
            $angkatanByKelasAndYear = [];
            
            foreach ($academicYears as $year) {
                $yearData = $this->processYearData(
                    $dosenId, 
                    $year, 
                    $currentSem, 
                    $kelasList, 
                    $kelasAngkatanMap, 
                    $apiToken, 
                    $baseUrl
                );
                
                $studentsByYear[$year] = $yearData['students'] ?? [];
                $prodisByYear[$year] = $yearData['prodis'] ?? [];
                $semesterAveragesByYear[$year] = $yearData['semesterAverages'] ?? [];
                $angkatanByKelasAndYear[$year] = $yearData['angkatanByKelas'] ?? [];
            }

            $perwalianAnnouncement = $this->checkPerwalian($nip, $apiToken, $baseUrl);
            $allAcademicYears = [2017, 2018, 2019, 2020];
            
            return view('beranda.homeDosen', compact(
                'studentsByYear', 
                'prodisByYear', 
                'angkatanByKelasAndYear', 
                'perwalianAnnouncement', 
                'semesterAveragesByYear',
                'allAcademicYears'
            ));
            
        } catch (\Exception $e) {
            Log::error('Error fetching data in beranda:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An error occurred while fetching data: ' . $e->getMessage());
        }
    }

    public function fetchYearData(Request $request)
    {
        $year = $request->input('year');
        $apiToken = session('api_token');
        $nip = session('user')['nip'];
        $baseUrl = 'https://cis-dev.del.ac.id';
        $currentSem = 2;

        try {
            $dosenData = $this->fetchDosenData($apiToken, $baseUrl, $nip);
            $dosenId = $dosenData['pegawai_id'] ?? null;
            if (!$dosenId) {
                return response()->json(['error' => 'Dosen ID not found'], 400);
            }

            $dosenWali = DB::table('dosen_wali')->where('username', $nip)->first();
            if (!$dosenWali) {
                return response()->json(['error' => 'Dosen Wali record not found'], 400);
            }

            $kelasAngkatanMap = $this->parseKelasAngkatan($dosenWali);
            $kelasList = array_keys($kelasAngkatanMap);

            $yearData = $this->processYearData(
                $dosenId, 
                $year, 
                $currentSem, 
                $kelasList, 
                $kelasAngkatanMap, 
                $apiToken, 
                $baseUrl
            );

            return response()->json($yearData);
        } catch (\Exception $e) {
            Log::error('Error fetching year data:', [
                'year' => $year,
                'message' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to fetch year data'], 500);
        }
    }

    private function fetchDosenData($apiToken, $baseUrl, $nip)
    {
        $cacheKey = "dosen_data_{$nip}";
        
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
        
        Cache::put($cacheKey, $dosenSession, now()->addHours(24));
        
        return $dosenSession;
    }

    private function parseKelasAngkatan($dosenWali)
    {
        $kelasList = array_map('trim', explode(',', $dosenWali->kelas));
        $angkatanList = array_map('trim', explode(',', $dosenWali->angkatan));

        $kelasAngkatanMap = [];
        foreach ($kelasList as $index => $kelas) {
            $angkatan = isset($angkatanList[$index]) ? $angkatanList[$index] : end($angkatanList);
            $kelasAngkatanMap[$kelas] = $angkatan;
        }
        
        return $kelasAngkatanMap;
    }

    private function processYearData($dosenId, $year, $currentSem, $kelasList, $kelasAngkatanMap, $apiToken, $baseUrl)
    {
        $cacheKey = "year_data_{$dosenId}_{$year}_{$currentSem}";
        
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
                'semzameAverages' => [],
                'angkatanByKelas' => [],
            ];
        }

        $studentsByKelas = [];
        $angkatanByKelas = [];
        
        foreach ($students as $student) {
            $kelas = $student['kelas'] ?? null;
            if (is_null($kelas) || empty($kelas) || !in_array($kelas, $kelasList)) {
                continue;
            }
            
            $studentsByKelas[$kelas][] = $student;
            $angkatanByKelas[$kelas] = $kelasAngkatanMap[$kelas] ?? $year;
        }
        
        $result = [
            'students' => [],
            'prodis' => [],
            'semesterAverages' => [],
            'angkatanByKelas' => $angkatanByKelas,
        ];
        
        foreach ($studentsByKelas as $kelas => $classStudents) {
            $penilaianDataBatch = $this->batchFetchPenilaian($classStudents, $apiToken, $baseUrl, $year, $currentSem);
            
            $semesterTotals = [];
            $processedStudents = $this->processStudentsData($classStudents, $penilaianDataBatch, $kelas, $semesterTotals);
            
            $result['students'][$kelas] = $processedStudents;
            $result['prodis'][$kelas] = $this->kelasToProdi($kelas) ?? null;
            
            $averages = [];
            foreach ($semesterTotals as $sem => $data) {
                if ($data['count'] > 0) {
                    $averages[$sem] = number_format($data['total'] / $data['count'], 2);
                }
            }
            $result['semesterAverages'][$kelas] = $averages;
        }
        
        Cache::put($cacheKey, $result, now()->addHours(24));
        
        return $result;
    }

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
            $latestIp = null;
            $latestSem = null;
            
            foreach ($ipSemesterData as $entry) {
                if (isset($entry['ip_semester']) && isset($entry['sem']) && 
                    is_numeric($entry['ip_semester']) && 
                    $entry['ip_semester'] !== "Belum di-generate") {
                    $sem = $entry['sem'];
                    $ip = floatval($entry['ip_semester']);
                    
                    if ($latestSem === null || $sem > $latestSem) {
                        $latestSem = $sem;
                        $latestIp = $entry['ip_semester'];
                    }

                    if (!isset($semesterTotals[$sem])) {
                        $semesterTotals[$sem] = ['total' => 0, 'count' => 0];
                    }
                    $semesterTotals[$sem]['total'] += $ip;
                    $semesterTotals[$sem]['count'] += 1;
                }
            }

            $ips = $latestIp;
            $semester = $latestSem ?? 1;

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

        if (!session('user') || seszsion('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $apiToken = session('api_token');
        $nip = session('user')['nip'];
        $baseUrl = 'https://cis-dev.del.ac.id';
        $students = [];
        $currentSem = 2;

        if ($apiToken) {
            try {
                $dosenData = $this->fetchDosenData($apiToken, $baseUrl, $nip);
                if (!$dosenData) {
                    return back()->with('error', 'Failed to fetch lecturer data.');
                }

                $dosenId = $dosenData['pegawai_id'] ?? null;
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

                    $latestIp = null;
                    $latestSem = null;
                    foreach ($ipSemesterData as $entry) {
                        if (!is_array($entry)) {
                            Log::warning("IP Semester entry is not an array for student {$nim}", ['entry' => $entry]);
                            continue;
                        }
                        if (isset($entry['ip_semester']) && isset($entry['sem']) && 
                            is_numeric($entry['ip_semester']) && 
                            $entry['ip_semester'] !== "Belum di-generate") {
                            $sem = $entry['sem'];
                            if ($latestSem === null || $sem > $latestSem) {
                                $latestSem = $sem;
                                $latestIp = $entry['ip_semester'];
                            }
                        }
                    }

                    $ips = $latestIp;
                    $statusKrs = $penilaianData['status_krs'] ?? null;
                    $semester = $latestSem ?? $currentSem;

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

        $batchSize = 50;
        $nimChunks = array_chunk($nims, $batchSize);
        $promises = [];

        foreach ($nimChunks as $index => $nimChunk) {
            $chunkKey = "penilaian_chunk_" . md5(implode(',', $nimChunk)) . "_{$year}_{$semester}";
            $cachedChunk = Cache::get($chunkKey);

            if ($cachedChunk) {
                $penilaianDataBatch = array_merge($penilaianDataBatch, $cachedChunk);
                continue;
            }

            $queryParams = [];
            foreach ($nimChunk as $i => $nim) {
                $queryParams["nims[{$i}]"] = $nim;
            }

            $promises[$index] = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->async()
                ->get("{$baseUrl}/api/library-api/get-penilaian", $queryParams);
        }

        $results = Utils::settle($promises)->wait();

        foreach ($results as $index => $result) {
            $nimChunk = $nimChunks[$index];
            $chunkKey = "penilaian_chunk_" . md5(implode(',', $nimChunk)) . "_{$year}_{$semester}";
            $chunkData = [];

            if ($result['state'] === 'fulfilled' && $result['value']->successful()) {
                $responseData = $result['value']->json();
                
                foreach ($nimChunk as $nim) {
                    $penilaianData = $responseData[$nim] ?? [
                        'IP' => '0.00',
                        'IP Semester' => [],
                        'status_krs' => 'Approved',
                    ];
                    $penilaianDataBatch[$nim] = $penilaianData;
                    $chunkData[$nim] = $penilaianData;
                }
            } else {
                Log::warning("Batch fetch failed for chunk {$index}, falling back to individual requests", [
                    'status' => $result['value'] ? $result['value']->status() : 'N/A',
                    'response' => $result['value'] ? $result['value']->body() : $result['reason'],
                ]);

                $individualPromises = [];
                foreach ($nimChunk as $nim) {
                    $cacheKey = "penilaian_{$nim}_{$year}_{$semester}";
                    $cachedData = Cache::get($cacheKey);

                    if ($cachedData) {
                        $penilaianDataBatch[$nim] = $cachedData;
                        $chunkData[$nim] = $cachedData;
                        continue;
                    }

                    $individualPromises[$nim] = Http::withToken($apiToken)
                        ->withOptions(['verify' => false])
                        ->timeout(15)
                        ->async()
                        ->get("{$baseUrl}/api/library-api/get-penilaian", ['nim' => $nim]);
                }

                $individualResults = Utils::settle($individualPromises)->wait();
                foreach ($individualResults as $nim => $indResult) {
                    if ($indResult['state'] === 'fulfilled' && $indResult['value']->successful()) {
                        $penilaianData = $indResult['value']->json();
                        $penilaianDataBatch[$nim] = $penilaianData;
                        $chunkData[$nim] = $penilaianData;
                        Cache::put("penilaian_{$nim}_{$year}_{$semester}", $penilaianData, now()->addHours(24));
                    } else {
                        $penilaianDataBatch[$nim] = [
                            'IP' => '0.00',
                            'IP Semester' => [],
                            'status_krs' => 'Approved',
                        ];
                        $chunkData[$nim] = $penilaianDataBatch[$nim];
                        Log::warning("Failed to fetch penilaian data for student {$nim}", [
                            'status' => $indResult['value'] ? $indResult['value']->status() : 'N/A',
                            'response' => $indResult['value'] ? $indResult['value']->body() : $indResult['reason'],
                        ]);
                    }
                }
            }

            Cache::put($chunkKey, $chunkData, now()->addHours(24));
        }
        
        return $penilaianDataBatch;
    }

    private function checkPerwalian($dosenId, $apiToken, $baseUrl)
    {
        $user = session('user');
        try {
            if (!isset($user['nip'])) {
                Log::error('NIP not found in session for checkPerwalian', ['user' => $user]);
                return null;
            }

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