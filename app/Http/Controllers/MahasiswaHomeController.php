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

        // Debug: Log the logged-in student's NIM
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
        // Debug: Dump the final attendance data
        // dd($attendanceData);

        $akademik = Calendar::where('type', 'akademik')->latest()->first();
        $bem = Calendar::where('type', 'bem')->latest()->first();

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
            'mahasiswa'
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
            // Step 1: Fetch Absensi records for the logged-in student only
            $absensiRecords = Absensi::where('nim', $nim)->get();

            // Debug: Log the retrieved records
            Log::info('Absensi Records for Student:', ['nim' => $nim, 'records' => $absensiRecords->toArray()]);

            if ($absensiRecords->isEmpty()) {
                Log::warning('No Absensi records found for student:', ['nim' => $nim]);
                return [
                    'dates' => ['28/04/25', '29/04/25'],
                    'values' => [-0.5, -0.5],
                    'colors' => ['#dc3545', '#dc3545'],
                ];
            }

            // Step 2: Group records by date
            $groupedByDate = $absensiRecords->groupBy(function ($record) {
                return \Carbon\Carbon::parse($record->tanggal)->format('d/m/y');
            })->filter(function ($group, $date) {
                return $date !== null;
            })->sortKeys();

            $dates = $groupedByDate->keys()->toArray();
            $values = [];
            $colors = [];

            // Debug: Log grouped data
            Log::info('Grouped Absensi Records by Date for Student:', ['nim' => $nim, 'grouped' => $groupedByDate->toArray()]);

            foreach ($groupedByDate as $date => $records) {
                $hadirCount = 0;
                $alpaCount = 0;

                foreach ($records as $record) {
                    // Normalize status_kehadiran for comparison
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

                // Debug: Log counts for this date
                Log::info('Attendance Counts for Date:', [
                    'date' => $date,
                    'hadir_count' => $hadirCount,
                    'alpa_count' => $alpaCount,
                ]);

                // Determine the status for this date
                if ($hadirCount > 0) {
                    $values[] = (float) 0.5; // Hadir or Izin (middle position)
                    $colors[] = '#007bff'; // Blue for Hadir
                } else {
                    $values[] = (float) -0.5; // Tidak Hadir (Alpa, below middle)
                    $colors[] = '#dc3545'; // Red for Tidak Hadir
                }
            }

            // If no data, return a default dataset with two points
            if (empty($dates)) {
                Log::warning('No dates found after grouping Absensi records for student:', ['nim' => $nim]);
                return [
                    'dates' => ['28/04/25', '29/04/25'],
                    'values' => [-0.5, -0.5],
                    'colors' => ['#dc3545', '#dc3545'],
                ];
            }

            // If only one date, add a dummy date (next day) to ensure line chart renders
            if (count($dates) === 1) {
                $singleDate = \Carbon\Carbon::createFromFormat('d/m/y', $dates[0]);
                $nextDate = $singleDate->addDay()->format('d/m/y');
                $dates[] = $nextDate;
                $values[] = $values[0]; // Repeat the same value
                $colors[] = $colors[0]; // Repeat the same color
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
}