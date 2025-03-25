<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Perwalian;

class DosenController extends Controller
{
    public function beranda()
    {
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
                // Step 1: Fetch dosen details
                $dosenResponse = Http::withToken($apiToken)
                    ->withOptions(['verify' => false])
                    ->timeout(5)
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
                session([
                    'user' => [
                        "username"                  => $nip,
                        "role"                      => 'dosen',
                        "pegawai_id"                => $dosenSession['pegawai_id'],
                        "dosen_id"                  => $dosenSession['dosen_id'],
                        "nip"                       => $dosenSession['nip'],
                        "nama"                      => $dosenSession['nama'],
                        "email"                     => $dosenSession['email'],
                        "prodi_id"                  => $dosenSession['prodi_id'],
                        "prodi"                     => $dosenSession['prodi'],
                        "jabatan_akademik"          => $dosenSession['jabatan_akademik'],
                        "jabatan_akademik_desc"     => $dosenSession['jabatan_akademik_desc'],
                        "jenjang_pendidikan"        => $dosenSession['jenjang_pendidikan'],
                        "nidn"                      => $dosenSession['nidn'],
                        "user_id"                   => $dosenSession['user_id'],
                    ],
                ]);
                $dosenId = $dosenData['data']['dosen'][0]['pegawai_id'] ?? null;
                if (!$dosenId) {
                    return back()->with('error', 'Dosen ID not found.');
                }

                // Step 2: Fetch anak wali for each year separately
                foreach ($academicYears as $year) {
                    $mahasiswaResponse = Http::withToken($apiToken)
                        ->withOptions(['verify' => false])
                        ->timeout(5)
                        ->get("{$baseUrl}/api/library-api/get-all-students-by-dosen-wali", [
                            'dosen_id' => $dosenId,
                            'ta'       => $year,
                            'sem_ta'   => $currentSem,
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

                                    // Fetch penilaian data with error handling
                                    $penilaianData = [];
                                    $ipSemesterData = [];
                                    try {
                                        $penilaianResponse = Http::withToken($apiToken)
                                            ->withOptions(['verify' => false])
                                            ->timeout(5)
                                            ->get("{$baseUrl}/api/library-api/get-penilaian", [
                                                'nim'     => $nim,
                                                'ta'      => $year,
                                                'sem_ta'  => $currentSem,
                                            ]);

                                        if ($penilaianResponse->successful()) {
                                            $penilaianData = $penilaianResponse->json();
                                            $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                                        } else {
                                            Log::warning("Failed to fetch penilaian data for student {$nim}", [
                                                'status' => $penilaianResponse->status(),
                                                'response' => $penilaianResponse->body(),
                                            ]);
                                            // Mock the penilaian data
                                            $penilaianData = [
                                                'IP Semester' => [
                                                    [
                                                        'ta' => '2020',
                                                        'sem_ta' => 2,
                                                        'sem' => 4,
                                                        'ip_semester' => '3.50',
                                                    ],
                                                    [
                                                        'ta' => '2020',
                                                        'sem_ta' => 1,
                                                        'sem' => 3,
                                                        'ip_semester' => '3.20',
                                                    ],
                                                ],
                                                'status_krs' => 'Approved',
                                            ];
                                            $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                                            Log::info("Using mocked penilaian data for student {$nim} due to API failure");
                                        }
                                    } catch (\Exception $e) {
                                        Log::error("Error fetching penilaian data for student {$nim}", [
                                            'message' => $e->getMessage(),
                                            'nim' => $nim,
                                            'year' => $year,
                                            'sem_ta' => $currentSem,
                                        ]);
                                        // Mock the penilaian data
                                        $penilaianData = [
                                            'IP Semester' => [
                                                [
                                                    'ta' => '2020',
                                                    'sem_ta' => 2,
                                                    'sem' => 4,
                                                    'ip_semester' => '3.50',
                                                ],
                                                [
                                                    'ta' => '2020',
                                                    'sem_ta' => 1,
                                                    'sem' => 3,
                                                    'ip_semester' => '3.20',
                                                ],
                                            ],
                                            'status_krs' => 'Approved',
                                        ];
                                        $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                                        Log::info("Using mocked penilaian data for student {$nim} due to API failure");
                                    }

                                    // Calculate IPK (cumulative GPA)
                                    $sum = 0;
                                    $count = 0;
                                    if (!is_array($ipSemesterData)) {
                                        Log::warning("IP Semester data is not an array for student {$nim}", ['ipSemesterData' => $ipSemesterData]);
                                        $ipSemesterData = [];
                                    }

                                    foreach ($ipSemesterData as $entry) {
                                        if (!is_array($entry)) {
                                            Log::warning("IP Semester entry is not an array for student {$nim}", ['entry' => $entry]);
                                            continue;
                                        }

                                        if (isset($entry['ip_semester'])) {
                                            $ipValue = ($entry['ip_semester'] === "Belum di-generate") ? 0 : $entry['ip_semester'];
                                            if (is_numeric($ipValue)) {
                                                $sum += floatval($ipValue);
                                                $count++;
                                            }
                                        }
                                    }
                                    $ipk = $count > 0 ? number_format($sum / $count, 2) : null;

                                    // Calculate IPS (most recent semester GPA)
                                    $validIps = [];
                                    foreach ($ipSemesterData as $entry) {
                                        if (!is_array($entry)) {
                                            Log::warning("IP Semester entry is not an array for student {$nim}", ['entry' => $entry]);
                                            continue;
                                        }

                                        if (isset($entry['ip_semester']) && isset($entry['sem'])) {
                                            if (!is_numeric($entry['sem'])) {
                                                Log::warning("Invalid semester value for student {$nim}, sem = {$entry['sem']}");
                                                continue;
                                            }
                                            if (is_numeric($entry['ip_semester'])) {
                                                $validIps[] = $entry;
                                            } elseif ($entry['ip_semester'] === "Belum di-generate") {
                                                Log::info("IP Semester not generated for student {$nim}, semester {$entry['sem']}");
                                            }
                                        }
                                    }
                                    $ips = null;
                                    if (!empty($validIps)) {
                                        usort($validIps, function($a, $b) {
                                            return $b['sem'] - $a['sem'];
                                        });
                                        $ips = $validIps[0]['ip_semester'];
                                    }

                                    $statusKrs = $penilaianData['status_krs'] ?? null;
                                    $semester = !empty($validIps) ? $validIps[0]['sem'] : $currentSem;

                                    $studentData = array_merge($student, [
                                        'ipk'         => $ipk,
                                        'ips'         => $ips,
                                        'status_krs'  => $statusKrs,
                                        'semester'    => $semester,
                                        'kelas'       => $kelas,
                                    ]);
                                    $classStudents[] = $studentData;
                                }

                                // Store this class's students under the year
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

                // Step 4: Check for perwalian schedules
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
                    ->timeout(5)
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
                    ->timeout(5)
                    ->get("{$baseUrl}/api/library-api/get-all-students-by-dosen-wali", [
                        'dosen_id' => $dosenId,
                        'ta'       => $currentTa,
                        'sem_ta'   => $currentSem,
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

                // Find the specific class
                $classes = $responseData['daftar_kelas'];
                $targetClass = null;
                foreach ($classes as $classData) {
                    if ($classData['kelas'] === $kelas) {
                        $targetClass = $classData;
                        break;
                    }
                }

                if (!$targetClass) {
                    return back()->with('error', 'Class not found.');
                }

                $studentsInClass = $targetClass['anak_wali'] ?? [];

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

                    // Fetch penilaian data with error handling
                    $penilaianData = [];
                    $ipSemesterData = [];
                    try {
                        $penilaianResponse = Http::withToken($apiToken)
                            ->withOptions(['verify' => false])
                            ->timeout(5)
                            ->get("{$baseUrl}/api/library-api/get-penilaian", [
                                'nim'     => $nim,
                                'ta'      => $currentTa, // Use the correct year
                                'sem_ta'  => $currentSem,
                            ]);

                        if ($penilaianResponse->successful()) {
                            $penilaianData = $penilaianResponse->json();
                            $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                        } else {
                            Log::warning("Failed to fetch penilaian data for student {$nim}", [
                                'status' => $penilaianResponse->status(),
                                'response' => $penilaianResponse->body(),
                            ]);
                            // Mock the penilaian data
                            $penilaianData = [
                                'IP Semester' => [
                                    [
                                        'ta' => '2020',
                                        'sem_ta' => 2,
                                        'sem' => 4,
                                        'ip_semester' => '3.50',
                                    ],
                                    [
                                        'ta' => '2020',
                                        'sem_ta' => 1,
                                        'sem' => 3,
                                        'ip_semester' => '3.20',
                                    ],
                                ],
                                'status_krs' => 'Approved',
                            ];
                            $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                            Log::info("Using mocked penilaian data for student {$nim} due to API failure");
                        }
                    } catch (\Exception $e) {
                        Log::error("Error fetching penilaian data for student {$nim}", [
                            'message' => $e->getMessage(),
                            'nim' => $nim,
                            'year' => $currentTa,
                            'sem_ta' => $currentSem,
                        ]);
                        // Mock the penilaian data
                        $penilaianData = [
                            'IP Semester' => [
                                [
                                    'ta' => '2020',
                                    'sem_ta' => 2,
                                    'sem' => 4,
                                    'ip_semester' => '3.50',
                                ],
                                [
                                    'ta' => '2020',
                                    'sem_ta' => 1,
                                    'sem' => 3,
                                    'ip_semester' => '3.20',
                                ],
                            ],
                            'status_krs' => 'Approved',
                        ];
                        $ipSemesterData = $penilaianData['IP Semester'] ?? [];
                        Log::info("Using mocked penilaian data for student {$nim} due to API failure");
                    }

                    // Calculate IPK (cumulative GPA)
                    $sum = 0;
                    $count = 0;
                    if (!is_array($ipSemesterData)) {
                        Log::warning("IP Semester data is not an array for student {$nim}", ['ipSemesterData' => $ipSemesterData]);
                        $ipSemesterData = [];
                    }

                    foreach ($ipSemesterData as $entry) {
                        if (!is_array($entry)) {
                            Log::warning("IP Semester entry is not an array for student {$nim}", ['entry' => $entry]);
                            continue;
                        }

                        if (isset($entry['ip_semester'])) {
                            $ipValue = ($entry['ip_semester'] === "Belum di-generate") ? 0 : $entry['ip_semester'];
                            if (is_numeric($ipValue)) {
                                $sum += floatval($ipValue);
                                $count++;
                            }
                        }
                    }
                    $ipk = $count > 0 ? number_format($sum / $count, 2) : null;

                    // Calculate IPS (most recent semester GPA)
                    $validIps = [];
                    foreach ($ipSemesterData as $entry) {
                        if (!is_array($entry)) {
                            Log::warning("IP Semester entry is not an array for student {$nim}", ['entry' => $entry]);
                            continue;
                        }

                        if (isset($entry['ip_semester']) && isset($entry['sem'])) {
                            if (!is_numeric($entry['sem'])) {
                                Log::warning("Invalid semester value for student {$nim}, sem = {$entry['sem']}");
                                continue;
                            }
                            if (is_numeric($entry['ip_semester'])) {
                                $validIps[] = $entry;
                            } elseif ($entry['ip_semester'] === "Belum di-generate") {
                                Log::info("IP Semester not generated for student {$nim}, semester {$entry['sem']}");
                            }
                        }
                    }
                    $ips = null;
                    if (!empty($validIps)) {
                        usort($validIps, function($a, $b) {
                            return $b['sem'] - $a['sem'];
                        });
                        $ips = $validIps[0]['ip_semester'];
                    }

                    $statusKrs = $penilaianData['status_krs'] ?? null;
                    $semester = !empty($validIps) ? $validIps[0]['sem'] : $currentSem;

                    $studentData = array_merge($student, [
                        'ipk'         => $ipk,
                        'ips'         => $ips,
                        'status_krs'  => $statusKrs,
                        'semester'    => $semester,
                        'kelas'       => $kelas,
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