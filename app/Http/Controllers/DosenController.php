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
        $studentsByYear = [];
        $prodisByYear = [];
        $semesterAveragesByYear = [];
        $angkatanByKelasAndYear = []; // New array to store angkatan for each year and kelas

        if ($apiToken) {
            try {
                // Fetch dosen details
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
                $dosenSession = $dosenData['data']['dosen'][0];
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

                $dosenId = $dosenData['data']['dosen'][0]['pegawai_id'] ?? null;
                if (!$dosenId) {
                    return back()->with('error', 'Dosen ID not found.');
                }

                // Fetch Dosen_Wali record for the current Dosen
                $dosenWali = DB::table('dosen_wali')->where('username', $nip)->first();
                if (!$dosenWali) {
                    return back()->with('error', 'Dosen Wali record not found for this lecturer.');
                }

                // Parse kelas and angkatan from dosen_wali
                $kelasList = array_map('trim', explode(',', $dosenWali->kelas));
                $angkatanList = array_map('trim', explode(',', $dosenWali->angkatan));

                // Create a mapping of kelas to angkatan
                $kelasAngkatanMap = [];
                foreach ($kelasList as $index => $kelas) {
                    $angkatan = isset($angkatanList[$index]) ? $angkatanList[$index] : end($angkatanList);
                    $kelasAngkatanMap[$kelas] = $angkatan;
                }

                $academicYears = [2017, 2018, 2019, 2020];
                $currentSem = 2;

                foreach ($academicYears as $year) {
                    $studentsByYear[$year] = [];
                    $prodisByYear[$year] = [];
                    $semesterAveragesByYear[$year] = [];
                    $angkatanByKelasAndYear[$year] = [];

                    $students = $this->studentSyncService->fetchStudents($dosenId, $year, $currentSem, null);

                    if (empty($students)) {
                        Log::warning("No students fetched for year {$year}", [
                            'dosen_id' => $dosenId,
                            'semester' => $currentSem,
                        ]);
                        continue;
                    }

                    $classStudentsByKelas = [];
                    foreach ($students as $student) {
                        $kelas = $student['kelas'] ?? null;
                        if (is_null($kelas) || empty($kelas)) {
                            Log::warning("Class name missing", ['student' => $student]);
                            continue;
                        }

                        // Only include students whose kelas is in dosen_wali
                        if (!in_array($kelas, $kelasList)) {
                            continue;
                        }

                        $classStudentsByKelas[$kelas][] = $student;
                        // Map angkatan for this kelas and year
                        $angkatanByKelasAndYear[$year][$kelas] = $kelasAngkatanMap[$kelas] ?? $year;
                    }

                    foreach ($classStudentsByKelas as $kelas => $classStudents) {
                        $semesterTotals = [];
                        $penilaianDataBatch = $this->batchFetchPenilaian($classStudents, $apiToken, $baseUrl, $year, $currentSem);

                        foreach ($classStudents as &$student) {
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
                            if (!empty($validIps)) {
                                usort($validIps, function ($a, $b) {
                                    return $b['sem'] - $a['sem'];
                                });
                                $ips = $validIps[0]['ip_semester'];
                            }

                            $statusKrs = $penilaianData['status_krs'] ?? null;
                            $semester = !empty($validIps) ? $validIps[0]['sem'] : $currentSem;

                            $student['ipk'] = $ipk;
                            $student['ips'] = $ips;
                            $student['status_krs'] = $statusKrs;
                            $student['semester'] = $semester;
                            $student['kelas'] = $kelas;
                        }

                        $studentsByYear[$year][$kelas] = $classStudents;
                        $prodisByYear[$year][$kelas] = $this->kelasToProdi($kelas) ?? null;

                        $averages = [];
                        foreach ($semesterTotals as $sem => $data) {
                            $averages[$sem] = number_format($data['total'] / $data['count'], 2);
                        }
                        $semesterAveragesByYear[$year][$kelas] = $averages;
                    }
                }

                $perwalianAnnouncement = $this->checkPerwalian($nip, $apiToken, $baseUrl);
                
            } catch (\Exception $e) {
                Log::error('Error fetching data in beranda:', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return back()->with('error', 'An error occurred while fetching data: ' . $e->getMessage());
            }
        }

        return view('beranda.homeDosen', compact('studentsByYear', 'prodisByYear', 'angkatanByKelasAndYear', 'perwalianAnnouncement', 'semesterAveragesByYear'));
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
                $dosenId = $dosenData['data']['dosen'][0]['dosen_id'] ?? null;
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