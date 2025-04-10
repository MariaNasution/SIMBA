<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Perwalian;
use App\Services\StudentSyncService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        $nip = session('user')['username'] ?? null;
        if (!$nip) {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $apiToken = session('api_token');
        $baseUrl = 'https://cis-dev.del.ac.id';
        $studentsByYear = [];
        $prodisByYear = [];
        $semesterAveragesByYear = [];
        $angkatanByKelasAndYear = [];

        if (!$apiToken) {
            return back()->with('error', 'API token not found.');
        }

        try {
            $dosenResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->get("{$baseUrl}/api/library-api/get-dosen-by-nip", [
                    "nip" => $nip,
                    "user_id" => session('user')['user_id'],
                ]);

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

            $dosenWali = DB::table('dosen_wali')->where('username', $nip)->first();
            if (!$dosenWali) {
                return back()->with('error', 'Dosen Wali record not found for this lecturer.');
            }

            $kelasList = array_map('trim', explode(',', $dosenWali->kelas));
            $angkatanList = array_map('trim', explode(',', $dosenWali->angkatan));
            $kelasAngkatanMap = [];
            foreach ($kelasList as $index => $kelas) {
                $angkatan = $angkatanList[$index] ?? end($angkatanList);
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

                    if (!in_array($kelas, $kelasList)) {
                        continue;
                    }

                    $classStudentsByKelas[$kelas][] = $student;
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
                            usort($validIps, fn($a, $b) => $b['sem'] - $a['sem']);
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
                    unset($student); // Unset reference after loop

                    $studentsByYear[$year][$kelas] = $classStudents;
                    $prodisByYear[$year][$kelas] = $this->kelasToProdi($kelas) ?? null;

                    $averages = [];
                    foreach ($semesterTotals as $sem => $data) {
                        $averages[$sem] = number_format($data['total'] / $data['count'], 2);
                    }
                    $semesterAveragesByYear[$year][$kelas] = $averages;
                }
            }

            $perwalianAnnouncement = $this->checkPerwalian($dosenId, $apiToken, $baseUrl);
        } catch (\Exception $e) {
            Log::error('Error in beranda method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An error occurred while fetching data.');
        }

        return view('beranda.homeDosen', compact('studentsByYear', 'prodisByYear', 'angkatanByKelasAndYear', 'perwalianAnnouncement', 'semesterAveragesByYear'));
    }

    public function showDetailedClass($year, $kelas)
    {
        $nip = session('user')['username'] ?? null;
        if (!$nip) {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $apiToken = session('api_token');
        $baseUrl = 'https://cis-dev.del.ac.id';
        $students = [];
        $currentSem = 2;

        if (!$apiToken) {
            return back()->with('error', 'API token not found.');
        }

        try {
            $dosenResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->get("{$baseUrl}/api/library-api/get-dosen-by-nip", [
                    "nip" => $nip,
                    "user_id" => session('user')['user_id'],
                ]);

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
                    'semester' => $currentSem,
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

                if (!isset($penilaianDataBatch[$nim])) {
                    Log::warning("No penilaian data found for student {$nim} in class {$kelas}");
                    continue;
                }

                $penilaianData = $penilaianDataBatch[$nim];
                $ipk = isset($penilaianData['IP']) && is_numeric($penilaianData['IP'])
                    ? number_format(floatval($penilaianData['IP']), 2)
                    : null;

                $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                $validIps = [];
                foreach ($ipSemesterData as $entry) {
                    if (isset($entry['ip_semester']) && isset($entry['sem']) &&
                        is_numeric($entry['ip_semester']) &&
                        $entry['ip_semester'] !== "Belum di-generate") {
                        $validIps[] = $entry;
                    }
                }

                $ips = null;
                if (!empty($validIps)) {
                    usort($validIps, fn($a, $b) => $b['sem'] - $a['sem']);
                    $ips = $validIps[0]['ip_semester'];
                }

                $statusKrs = $penilaianData['status_krs'] ?? null;
                $semester = !empty($validIps) ? $validIps[0]['sem'] : $currentSem;

                $students[] = array_merge($student, [
                    'ipk' => $ipk,
                    'ips' => $ips,
                    'status_krs' => $statusKrs,
                    'semester' => $semester,
                    'kelas' => $kelas,
                ]);
            }

            $perwalianAnnouncement = $this->checkPerwalian($dosenId, $apiToken, $baseUrl);
        } catch (\Exception $e) {
            Log::error('Error in showDetailedClass method', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'An error occurred while fetching class data.');
        }

        return view('dosen.detailedClass', compact('students', 'year', 'kelas', 'perwalianAnnouncement'));
    }

    private function batchFetchPenilaian($students, $apiToken, $baseUrl, $year, $semester)
    {
        $penilaianDataBatch = [];
        $nims = array_filter(array_column($students, 'nim'));

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
                ->get("{$baseUrl}/api/library-api/get-penilaian", ['nim' => $nim]);
        }

        foreach ($promises as $nim => $promise) {
            try {
                $response = $promise->wait();
                if ($response->successful()) {
                    $penilaianData = $response->json();
                    $penilaianDataBatch[$nim] = $penilaianData;
                    Cache::put($cacheKey, $penilaianData, now()->addHour());
                } else {
                    Log::warning("Failed to fetch penilaian data for student {$nim}", [
                        'status' => $response->status(),
                        'response' => $response->body(),
                    ]);
                    $penilaianDataBatch[$nim] = ['IP' => '0.00', 'IP Semester' => [], 'status_krs' => 'Approved'];
                }
            } catch (\Exception $e) {
                Log::error("Error fetching penilaian data for student {$nim}", [
                    'message' => $e->getMessage(),
                    'nim' => $nim,
                    'sem_ta' => $semester,
                ]);
                $penilaianDataBatch[$nim] = ['IP' => '0.00', 'IP Semester' => [], 'status_krs' => 'Approved'];
            }
        }

        return $penilaianDataBatch;
    }

    private function checkPerwalian($dosenId, $apiToken, $baseUrl)
    {
        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->timeout(15)
                ->get("{$baseUrl}/api/library-api/get-perwalian-status", ['dosen_id' => $dosenId]);

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            Log::warning('Failed to fetch perwalian status', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error checking perwalian status', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    private function kelasToProdi($kelas)
    {
        $prodiMap = [
            'IF' => 'S1Informatika',
            'TRPL' => 'S1TeknikRekayasaPerangkatLunak',
            'TK' => 'S1TeknikKomputer',
            'TI' => 'S1TeknikInformasi',
            'TB' => 'S1TeknikBioproses',
            'TM' => 'S1TeknikMetalurgi',
            'SI' => 'S1SistemInformasi',
            'TE' => 'S1TeknikElektro',
            'MR' => 'S1ManajemenRekayasa',
        ];

        foreach ($prodiMap as $prefix => $prodi) {
            if (str_contains($kelas, $prefix)) {
                return $prodi;
            }
        }
        return null;
    }
}