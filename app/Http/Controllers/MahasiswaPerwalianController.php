<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Notifikasi;
use App\Models\Absensi;
use App\Models\Perwalian;
use App\Models\Mahasiswa;
use App\Models\Dosen;

class MahasiswaPerwalianController extends Controller
{
    public function index()
    {
        // Get the API token from session
        $apiToken = session('api_token');

        // Fetch the student based on the nim from session
        $student = Mahasiswa::where('nim', session('user')['nim'] ?? null)->first();

        // Check if student exists
        if (!$student) {
            return redirect()->route('login')->with('error', 'Student not found. Please log in again.');
        }

        // Fetch the absensi record for the student
        $absensi = Absensi::where('nim', $student->nim)->latest('updated_at')->first();
        
        $perwalian = Perwalian::where('ID_Perwalian', $student->ID_Perwalian)
        ->orderBy('updated_at', 'desc')
        ->first();        $dosen = Dosen::where('nip', optional($perwalian)->ID_Dosen_Wali)->first(); // Safe access if $perwalian is null
        // Fetch all notifications with perwalian relationship to avoid N+1 problem
        $notifications = Notifikasi::with('perwalian') // Eager load perwalian
                                 ->where('Id_Perwalian', $student->ID_Perwalian)
                                 ->where('nim', $student->nim)
                                 ->get();

        // Get all ID_Dosen_Wali values from notifications (ensure perwalian relationship exists)
        $dosenWaliIds = $notifications->map(function ($notification) {
            return optional($notification->perwalian)->ID_Dosen_Wali;
        })->filter()->unique();

        // Filter dosen data to only include matches
        $dosenNotifications = $dosenWaliIds->isNotEmpty() ? Dosen::whereIn('nip', $dosenWaliIds)->get() : collect(); // Avoid query if empty

        // Get notification count
        $notificationCount = $notifications->count();
        // Debug: Uncomment to inspect data
        // dd($student, $absensi, $perwalian, $dosen, $notifications, $dosenNotifications);
        // dd($notifications[0]->perwalian->dosen);
        // Pass all necessary data to the view
        return view('mahasiswa.mahasiswa_perwalian', compact(
            
            'student',
            'absensi',
            'perwalian', // Ensure perwalian is included
            'dosenNotifications'
        ));
    }
}