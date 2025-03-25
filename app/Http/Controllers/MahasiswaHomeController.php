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
        // Get the authenticated user from session
        $user = session('user');
        $student = Mahasiswa::where('nim', session('user')['nim'] ?? null)->first();

        // Check if the user exists and has a mahasiswa role
        if (!$user || !isset($user['role']) || $user['role'] !== 'mahasiswa') {
            Log::error('User not authenticated or not a mahasiswa', ['user' => $user]);
            return redirect()->route('login')->withErrors(['error' => 'Please log in as a mahasiswa.']);
        }

        // Get the NIM from the mahasiswa table using the username from session
        $mahasiswa = Mahasiswa::where('username', $user['username'])->first();
        if (!$mahasiswa) {
            Log::error('No mahasiswa record found for user', ['username' => $user['username']]);
            return redirect()->route('login')->withErrors(['error' => 'Mahasiswa data not found.']);
        }
        $nim = $mahasiswa->nim;
        $apiToken = session('api_token');

        try {
            // Fetch student data from API
            $studentResponse = Http::withToken($apiToken)
                ->withOptions(['verify' => false])
                ->asForm()
                ->get('https://cis-dev.del.ac.id/api/library-api/get-student-by-nim', [
                    'nim' => $nim,
                ]);

            if ($studentResponse->successful()) {
                $studentData = $studentResponse->json()['data'] ?? [];
                session([
                    'sem_ta' => $studentData['sem_ta'] ?? null,
                    'ta' => $studentData['ta'] ?? null,
                ]);
            }

            // Fetch academic performance data from API
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
                
                foreach ($ipSemester as $semester => $details) {
                    $labels[] = "TA {$details['ta']} - Semester {$details['sem']}";
                    $values[] = is_numeric($details['ip_semester']) ? (float) $details['ip_semester'] : 0;
                }

                Log::info('Data labels:', $labels);
                Log::info('Data values:', $values);

                // Fetch the perwalian record for the student (only scheduled ones)
                $perwalian = Perwalian::where('ID_Perwalian', $mahasiswa->ID_Perwalian)
                    ->where('Status', 'Scheduled')
                    ->first();

                // Initialize variables to avoid undefined errors
                $dosen = null;
                $notifications = collect();
                $dosenNotifications = collect();
                $notificationCount = 0;
                $noPerwalianMessage = null;

                if ($perwalian) {
                    // Fetch the dosen for the perwalian
                    $dosen = Dosen::where('nip', $perwalian->ID_Dosen_Wali)->first();

                    // Fetch notifications for the perwalian
                    $notifications = Notifikasi::where('Id_Perwalian', $perwalian->ID_Perwalian)
                        ->where('nim', $mahasiswa->nim)
                        ->get();

                    // Get all ID_Dosen_Wali values from notifications
                    $dosenWaliIds = $notifications->map(function ($notification) {
                        return optional($notification->perwalian)->ID_Dosen_Wali;
                    })->filter()->unique();

                    // Fetch dosen data for notifications
                    $dosenNotifications = $dosenWaliIds->isNotEmpty() ? Dosen::whereIn('nip', $dosenWaliIds)->get() : collect();

                    // Get notification count
                    $notificationCount = $notifications->count();
                } else {
                    // Set a message to display in the view
                    $noPerwalianMessage = 'No scheduled perwalian sessions at this time.';
                    Log::info('No scheduled perwalian found for student', ['nim' => $nim]);
                }

                // Fetch absensi record (if needed, though it seems unused in the view)
                $absensi = Absensi::where('nim', $mahasiswa->nim)
                    ->where('ID_Perwalian', $mahasiswa->ID_Perwalian)
                    ->first();

                // Fetch announcements and calendar events
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
                    'noPerwalianMessage'
                ));
            }

            Log::error('Gagal mengambil data API', ['response' => $response->body()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Gagal mengambil data kemajuan studi.']);
        } catch (\Exception $e) {
            Log::error('Kesalahan API:', ['message' => $e->getMessage()]);
            return redirect()->route('beranda')->withErrors(['error' => 'Terjadi kesalahan saat memuat data.']);
        }
    }

    public function show($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        return view('beranda.detailpengumuman', compact('pengumuman'));
    }
}