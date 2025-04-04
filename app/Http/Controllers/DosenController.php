<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Perwalian;
use Illuminate\Support\Facades\Cache;

class DosenController extends Controller
{
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
        $academicYears = [2020];
        $currentSem = 2;

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

                // Fetch anak wali for each year
                foreach ($academicYears as $year) {
                    $mahasiswaResponse = Http::withToken($apiToken)
                        ->withOptions(['verify' => false])
                        ->timeout(15)
                        ->get("{$baseUrl}/api/library-api/get-all-students-by-dosen-wali", [
                            'dosen_id' => $dosenId,
                            'ta' => $year,
                            'sem_ta' => $currentSem,
                        ]);

                    $studentsByYear[$year] = [];
                    if ($mahasiswaResponse->successful()) {
                        $responseData = $mahasiswaResponse->json();

                        if (isset($responseData['daftar_kelas'])) {
                            $classes = $responseData['daftar_kelas'];

                            foreach ($classes as $classData) {
                                $kelas = $classData['kelas'] ?? null;
                                $students = $classData['anak_wali'] ?? [];
                                $classStudents = [];

                                if (is_null($kelas) || empty($kelas)) {
                                    Log::warning("Class name missing for year {$year}", ['classData' => $classData]);
                                    continue;
                                }

                                if (!is_array($students)) {
                                    Log::error("anak_wali is not an array for class {$kelas} in year {$year}", ['anak_wali' => $students]);
                                    continue;
                                }

                                // Batch fetch penilaian data
                                $penilaianDataBatch = $this->batchFetchPenilaian($students, $apiToken, $baseUrl, $year, $currentSem);

                                foreach ($students as $student) {
                                    if (!is_array($student)) {
                                        Log::warning("Student data is not an array for class {$kelas} in year {$year}", ['student' => $student]);
                                        continue;
                                    }

                                    $nim = $student['nim'] ?? null;
                                    if (!$nim) {
                                        Log::warning("Student NIM is missing for class {$kelas} in year {$year}", ['student' => $student]);
                                        continue;
                                    }

                                    if (!isset($student['nama'])) {
                                        Log::warning("Student name is missing for NIM {$nim}", ['student' => $student]);
                                        continue;
                                    }

                                    // Use batched penilaian data
                                    $penilaianData = $penilaianDataBatch[$nim] ?? [
                                        'IP' => '0.00',
                                        'IP Semester' => [],
                                        'status_krs' => 'Approved',
                                    ];

                                    // Calculate IPK and IPS
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
                                    $classStudents[] = $studentData;
                                }

                                $studentsByYear[$year][$kelas] = $classStudents;
                            }
                        }
                    } else {
                        Log::warning("Failed to fetch anak wali for year {$year}", [
                            'status' => $mahasiswaResponse->status(),
                            'response' => $mahasiswaResponse->body(),
                        ]);
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

        return view('beranda.homeDosen', compact('studentsByYear', 'perwalianAnnouncement'));
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
        $currentTa = $year;
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

                $mahasiswaResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->timeout(15)
                    ->get("{$baseUrl}/api/library-api/get-all-students-by-dosen-wali", [
                        'dosen_id' => $dosenId,
                        'ta' => $currentTa,
                        'sem_ta' => $currentSem,
                    ]);

                if (!$mahasiswaResponse->successful()) {
                    Log::error('Failed to fetch anak wali data', [
                        'status' => $mahasiswaResponse->status(),
                        'response' => $mahasiswaResponse->body(),
                    ]);
                    return back()->with('error', 'Failed to fetch student data.');
                }

                $responseData = $mahasiswaResponse->json();
                if (!isset($responseData['daftar_kelas'])) {
                    Log::error('Daftar kelas data not found in response', ['response' => $responseData]);
                    return back()->with('error', 'No class data found.');
                }

                $targetClass = null;
                foreach ($responseData['daftar_kelas'] as $classData) {
                    if ($classData['kelas'] === $kelas) {
                        $targetClass = $classData;
                        break;
                    }
                }

                if (!$targetClass) {
                    return back()->with('error', 'Class not found.');
                }

                $studentsInClass = $targetClass['anak_wali'] ?? [];
                $penilaianDataBatch = $this->batchFetchPenilaian($studentsInClass, $apiToken, $baseUrl, $currentTa, $currentSem);

                foreach ($studentsInClass as $student) {
                    if (!is_array($student)) {
                        Log::warning("Student data is not an array for class {$kelas} in year {$year}", ['student' => $student]);
                        continue;
                    }

                    $nim = $student['nim'] ?? null;
                    if (!$nim) {
                        Log::warning("Student NIM is missing for class {$kelas} in year {$year}", ['student' => $student]);
                        continue;
                    }

                    if (!isset($student['nama'])) {
                        Log::warning("Student name is missing for NIM {$nim}", ['student' => $student]);
                        continue;
                    }

                    // Use batched penilaian data
                    $penilaianData = $penilaianDataBatch[$nim] ?? [
                        'IP' => '0.00',
                        'IP Semester' => [],
                        'status_krs' => 'Approved',
                    ];

                    // Calculate IPK and IPS
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
            $cacheKey = "penilaian_{$nim}_{$year}_{$semester}";
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
                    'ta' => $year,
                    'sem_ta' => $semester,
                ]);
        }

        foreach ($promises as $nim => $promise) {
            try {
                $response = $promise->wait();
                if ($response->successful()) {
                    $penilaianData = $response->json();
                    $penilaianDataBatch[$nim] = $penilaianData;
                    Cache::put("penilaian_{$nim}_{$year}_{$semester}", $penilaianData, now()->addHour());
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
                    'year' => $year,
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

    public function index()
    {
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $dosenId = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenId)
            ->where('role', 'mahasiswa')
            ->select('username', 'name', 'semester', 'ipk', 'ips', 'status_krs')
            ->get()
            ->toArray();

        return view('dosen.index', compact('anakWali'));
    }

    public function presensi()
    {
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $dosenId = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenId)
            ->where('role', 'mahasiswa')
            ->select('username', 'name', 'semester', 'ipk', 'ips', 'status_krs')
            ->get();

        return view('dosen.presensi', compact('anak_wali'));
    }

    public function setPerwalian()
    {
        if (!session('user') || session('user')['role'] !== 'dosen') {
            return redirect()->route('login')->with('error', 'Unauthorized access.');
        }

        $dosenId = session('user')['username'];
        $anakWali = DB::table('users')
            ->where('anak_wali', $dosenId)
            ->where('role', 'mahasiswa')
            ->select('username', 'name', 'semester', 'ipk', 'ips', 'status_krs')
            ->get()
            ->toArray();

        return view('perwalian.setPerwalian', compact('anakWali'));
    }

    private function checkPerwalian($dosenId, $apiToken, $baseUrl)
    {
        try {
            $perwalian = Perwalian::where('ID_Dosen_Wali', $dosenId)
                ->where('Status', 'ongoing')
                ->orWhere('Tanggal', '>=', now()->toDateString())
                ->get();

            if ($perwalian->isNotEmpty()) {
                $announcements = [];
                foreach ($perwalian as $p) {
                    $date = \Carbon\Carbon::parse($p->Tanggal)->format('D, d/m/Y');
                    $announcements[] = "Jadwal Perwalian classes 13 IF 1 ({$date})";
                }
                return implode("\n", $announcements);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error checking perwalian:', ['message' => $e->getMessage()]);
            return null;
        }
    }
}