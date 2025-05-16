<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Absensi;
use App\Models\Perwalian;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MahasiswaHomeController extends Controller
{
    public function index()
    {
        $user = session('user');

        if (!$this->isValidMahasiswa($user)) {
            Log::error('User not authenticated or not a mahasiswa', ['user' => $user]);
            return redirect()->route('login')->withErrors(['error' => 'Please log in as a mahasiswa.']);
        }

        $mahasiswa = Mahasiswa::where('username', $user['username'])->first();
        if (!$mahasiswa) {
            Log::error('No mahasiswa record found for user', ['username' => $user['username']]);
            return redirect()->route('login')->withErrors(['error' => 'Mahasiswa data not found.']);
        }

        $nim = $mahasiswa->nim;
        $apiToken = session('api_token');

        Log::info('Logged-in Student NIM:', ['nim' => $nim]);

        $studentData = $this->fetchStudentData($nim, $apiToken);

        $performance = $this->fetchPenilaianData($nim, $apiToken);

        if (!$performance) {
            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data kemajuan studi.']);
        }
        list($labels, $values) = $performance;

        list(
            $dosen,
            $notifications,
            $dosenNotifications,
            $notificationCount,
            $noPerwalianMessage
        ) = $this->handlePerwalian($mahasiswa);

        $attendanceData = $this->fetchAttendanceFrequency($nim, $mahasiswa->ID_Perwalian);

        $akademik = Calendar::where('type', 'akademik')->latest()->first();
        $bem = Calendar::where('type', 'bem')->latest()->first();

        // Fetch advertisements from Go API
        $advertisements = $this->fetchAdvertisements();
        $adError = empty($advertisements); // Set error flag if no posts

        return view('beranda.homeMahasiswa', compact(
            'labels',
            'values',
            'attendanceData',
            'akademik',
            'bem',
            'notifications',
            'notificationCount',
            'dosenNotifications',
            'noPerwalianMessage',
            'mahasiswa',
            'advertisements',
            'adError'
        ));
    }

    private function isValidMahasiswa($user): bool
    {
        return $user && isset($user['role']) && $user['role'] === 'mahasiswa';
    }

    private function fetchStudentData(string $nim, string $apiToken): array
    {
        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->asForm()
                ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                    'nim' => $nim,
                ]);

            if ($response->successful()) {
                $studentData = $response->json()['data'] ?? [];
                session([
                    'sem_ta' => $studentData['sem_ta'] ?? null,
                    'ta' => $studentData['ta'] ?? null,
                ]);
                return $studentData;
            } else {
                Log::error('Student API request failed', ['status' => $response->status(), 'nim' => $nim]);
            }
        } catch (\Exception $e) {
            Log::error('Exception on fetching student data:', ['message' => $e->getMessage(), 'nim' => $nim]);
        }
        return [];
    }

    private function fetchPenilaianData(string $nim, string $apiToken): ?array
    {
        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->asForm()
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $nim,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $ipSemester = $data['IP Semester'] ?? [];
                uasort($ipSemester, function ($a, $b) {
                    if ($a['ta'] === $b['ta']) {
                        return $a['sem'] <=> $b['sem'];
                    }
                    return $a['ta'] <=> $b['ta'];
                });

                $labels = [];
                $values = [];

                foreach ($ipSemester as $details) {
                    $labels[] = "TA {$details['ta']} - Semester {$details['sem']}";
                    $values[] = is_numeric($details['ip_semester']) ? (float) $details['ip_semester'] : 0;
                }

                return [$labels, $values];
            } else {
                Log::error('Penilaian API request failed', ['response' => $response->body(), 'nim' => $nim]);
            }
        } catch (\Exception $e) {
            Log::error('Exception on fetching penilaian data:', ['message' => $e->getMessage(), 'nim' => $nim]);
        }

        return null;
    }

    private function fetchAttendanceFrequency(string $nim, ?string $idPerwalian): array
    {
        try {
            $absensiRecords = Absensi::where('nim', $nim)->get();

            Log::info('Absensi Records for Student:', ['nim' => $nim, 'records' => $absensiRecords->toArray()]);

            if ($absensiRecords->isEmpty()) {
                Log::warning('No Absensi records found for student:', ['nim' => $nim]);
                return [
                    'dates' => ['28/04/25', '29/04/25'],
                    'values' => [-0.5, -0.5],
                    'colors' => ['#dc3545', '#dc3545'],
                ];
            }

            $groupedByDate = $absensiRecords->groupBy(function ($record) {
                return \Carbon\Carbon::parse($record->tanggal)->format('d/m/y');
            })->filter(function ($group, $date) {
                return $date !== null;
            })->sortKeys();

            $dates = $groupedByDate->keys()->toArray();
            $values = [];
            $colors = [];

            Log::info('Grouped Absensi Records by Date for Student:', ['nim' => $nim, 'grouped' => $groupedByDate->toArray()]);

            foreach ($groupedByDate as $date => $records) {
                $hadirCount = 0;
                $alpaCount = 0;

                foreach ($records as $record) {
                    $status = strtolower(trim($record->status_kehadiran));
                    Log::info('Processing Record:', ['date' => $date, 'nim' => $record->nim, 'status' => $status]);

                    if (in_array($status, ['hadir', 'izin'], true)) {
                        $hadirCount++;
                    } elseif ($status === 'alpa') {
                        $alpaCount++;
                    } else {
                        Log::warning('Unexpected status_kehadiran value:', ['status' => $record->status_kehadiran]);
                    }
                }

                Log::info('Attendance Counts for Date:', [
                    'date' => $date,
                    'hadir_count' => $hadirCount,
                    'alpa_count' => $alpaCount,
                ]);

                if ($hadirCount > 0) {
                    $values[] = (float) 0.5;
                    $colors[] = '#007bff';
                } else {
                    $values[] = (float) -0.5;
                    $colors[] = '#dc3545';
                }
            }

            if (empty($dates)) {
                Log::warning('No dates found after grouping Absensi records for student:', ['nim' => $nim]);
                return [
                    'dates' => ['28/04/25', '29/04/25'],
                    'values' => [-0.5, -0.5],
                    'colors' => ['#dc3545', '#dc3545'],
                ];
            }

            if (count($dates) === 1) {
                $singleDate = \Carbon\Carbon::createFromFormat('d/m/y', $dates[0]);
                $nextDate = $singleDate->addDay()->format('d/m/y');
                $dates[] = $nextDate;
                $values[] = $values[0];
                $colors[] = $colors[0];
            }

            return [
                'dates' => $dates,
                'values' => $values,
                'colors' => $colors,
            ];
        } catch (\Exception $e) {
            Log::error('Exception on fetching attendance frequency:', ['message' => $e->getMessage(), 'nim' => $nim]);
            return [
                'dates' => ['28/04/25', '29/04/25'],
                'values' => [-0.5, -0.5],
                'colors' => ['#dc3545', '#dc3545'],
            ];
        }
    }

    private function handlePerwalian(Mahasiswa $mahasiswa): array
    {
        $dosen = null;
        $notifications = $mahasiswa->notifications()->orderBy('created_at', 'desc')->get();
        $notificationCount = $notifications->count();
        $dosenNotifications = collect();
        $noPerwalianMessage = null;

        $perwalian = Perwalian::where('ID_Perwalian', $mahasiswa->ID_Perwalian)
            ->where('Status', 'Scheduled')
            ->first();

        if ($perwalian) {
            $dosen = Dosen::where('nip', $perwalian->ID_Dosen_Wali)->first();

            $dosenWaliIds = $notifications->map(function ($notification) {
                return $notification->data['ID_Dosen_Wali'] ?? null;
            })->filter()->unique();

            $dosenNotifications = $dosenWaliIds->isNotEmpty()
                ? Dosen::whereIn('nip', $dosenWaliIds)->get()
                : collect();
        } else {
            $noPerwalianMessage = 'No scheduled perwalian sessions at this time.';
            Log::info('No scheduled perwalian found for student', ['nim' => $mahasiswa->nim]);
        }

        return [$dosen, $notifications, $dosenNotifications, $notificationCount, $noPerwalianMessage];
    }

    public function fetchAdvertisements(): array
    {
        try {
            $response = Http::get('http://localhost:8080/posts');

            if ($response->successful()) {
                $posts = $response->json();
                Log::info('Fetched advertisements from Go API', ['count' => count($posts)]);
                // Sort by created_at descending and limit to 10 posts
                usort($posts, function ($a, $b) {
                    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
                });
                return array_slice($posts, 0, 10); // Strictly 10 posts
            } else {
                Log::error('Go API request failed', ['status' => $response->status(), 'response' => $response->body()]);
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Exception on fetching advertisements:', ['message' => $e->getMessage()]);
            return [];
        }
    }
}