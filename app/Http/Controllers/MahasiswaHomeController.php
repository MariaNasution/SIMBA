<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Pengumuman;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Absensi;
use App\Models\Perwalian;
use App\Models\Mahasiswa;
use App\Models\Dosen;

class MahasiswaHomeController extends Controller
{
    public function index()
    {
        $user = session('user');

        // Validate user as mahasiswa
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
        $notifications = Notifikasi::where('nim', $nim)->latest()->get();
        $apiToken = session('api_token');

        // Fetch student data and store related session data
        $studentData = $this->fetchStudentData($nim, $apiToken);

        // Fetch academic performance data
        $performance = $this->fetchPenilaianData($nim, $apiToken);
        if (!$performance) {
            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data kemajuan studi.']);
        }
        list($labels, $values) = $performance;

        // Handle perwalian and notifications
        list(
            $dosen,
            $notifications,
            $dosenNotifications,
            $notificationCount,
            $noPerwalianMessage
        ) = $this->handlePerwalian($mahasiswa);

        // Additional data
        $absensi = Absensi::where('nim', $mahasiswa->nim)
            ->where('ID_Perwalian', $mahasiswa->ID_Perwalian)
            ->first();

        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->get();
        $akademik = Calendar::where('type', 'akademik')->latest()->first();
        $bem = Calendar::where('type', 'bem')->latest()->first();

        return view('beranda.homeMahasiswa', compact(
            'labels',
            'values',
            'pengumuman',
            'akademik',
            'bem',
            'notifications',
            'notificationCount',
            'dosenNotifications',
            'noPerwalianMessage',
            'mahasiswa'
        ));
    }

    /**
     * Validate that the user exists and has a mahasiswa role.
     *
     * @param mixed $user
     * @return bool
     */
    private function isValidMahasiswa($user): bool
    {
        return $user && isset($user['role']) && $user['role'] === 'mahasiswa';
    }

    /**
     * Fetch student data from the API and store sem_ta and ta in session.
     *
     * @param string $nim
     * @param string $apiToken
     * @return array
     */
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
                    'ta'     => $studentData['ta'] ?? null,
                ]);
                return $studentData;
            } else {
                Log::error('Student API request failed', ['status' => $response->status()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception on fetching student data:', ['message' => $e->getMessage()]);
        }
        return [];
    }

    /**
     * Fetch academic performance data (penilaian) from the API.
     *
     * @param string $nim
     * @param string $apiToken
     * @return array|null Returns an array containing labels and values or null if failed.
     */
    private function fetchPenilaianData(string $nim, string $apiToken): ?array
    {
        try {
            $response = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->asForm()
                ->get('https://cis-dev.del.ac.id/api/library-api/get-penilaian', [
                    'nim' => $nim,
                ]);

            Log::info('Respons API mentah:', ['body' => $response->body()]);

            if ($response->successful()) {
                $data = $response->json();
                $ipSemester = $data['IP Semester'] ?? [];

                // Sort by academic year (ta) and semester (sem)
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
                    $values[] = is_numeric($details['ip_semester']) ? (float)$details['ip_semester'] : 0;
                }

                Log::info('Data labels:', $labels);
                Log::info('Data values:', $values);

                return [$labels, $values];
            } else {
                Log::error('Gagal mengambil data API', ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error('Exception on fetching penilaian data:', ['message' => $e->getMessage()]);
        }
        return null;
    }

    /**
     * Handle the perwalian-related logic including fetching dosen data and notifications.
     *
     * @param Mahasiswa $mahasiswa
     * @return array An array containing: dosen, notifications, dosenNotifications, notificationCount, noPerwalianMessage.
     */
    private function handlePerwalian(Mahasiswa $mahasiswa): array
    {
        $dosen = null;
        $notifications = collect();
        $dosenNotifications = collect();
        $notificationCount = 0;
        $noPerwalianMessage = null;

        $perwalian = Perwalian::where('ID_Perwalian', $mahasiswa->ID_Perwalian)
            ->where('Status', 'Scheduled')
            ->first();

        if ($perwalian) {
            $dosen = Dosen::where('nip', $perwalian->ID_Dosen_Wali)->first();
            $notifications = Notifikasi::where('Id_Perwalian', $perwalian->ID_Perwalian)
                ->where('nim', $mahasiswa->nim)
                ->get();

            $dosenWaliIds = $notifications->map(function ($notification) {
                return optional($notification->perwalian)->ID_Dosen_Wali;
            })->filter()->unique();

            $dosenNotifications = $dosenWaliIds->isNotEmpty() 
                ? Dosen::whereIn('nip', $dosenWaliIds)->get() 
                : collect();

            $notificationCount = $notifications->count();
        } else {
            $noPerwalianMessage = 'No scheduled perwalian sessions at this time.';
            Log::info('No scheduled perwalian found for student', ['nim' => $mahasiswa->nim]);
        }

        return [$dosen, $notifications, $dosenNotifications, $notificationCount, $noPerwalianMessage];
    }

    /**
     * Show details for a given announcement.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        return view('beranda.detailpengumuman', compact('pengumuman'));
    }
}