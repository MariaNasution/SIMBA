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
        $absensi = Absensi::where('ID_Absensi', $student->ID_Absensi)->first();
        $perwalian = Perwalian::where('ID_Perwalian', $student->ID_Absensi)->first();
        $dosen = Dosen::where('nip', $perwalian->ID_Dosen_Wali)->first();
        // Fetch all notifications
        $notifications = Notifikasi::where('ID_Perwalian', $student->ID_Absensi);

        // Fetch dosen data from API
        $dosenResponse = Http::withToken($apiToken)
            ->withOptions(['verify' => false])
            ->asForm()
            ->get('https://cis-dev.del.ac.id/api/library-api/dosen');

        // Check if the API request was successful
        if ($dosenResponse->failed()) {
            $dosen = collect(); // Empty collection if API fails
        } else {
            $dosen = collect($dosenResponse->json());
        }

        dd($dosenResponse);        

        // Get all ID_Dosen_Wali values from notifications (ensure perwalian relationship exists)
        $dosenWaliIds = $notifications->map(function ($notification) {
            return optional($notification->perwalian)->ID_Dosen_Wali;
        })->filter()->unique();

        // Filter dosen data to only include matches
        $dosenNotifications = $dosen->whereIn('nip', $dosenWaliIds)->values();

        // Get notification count
        $notificationCount = $notifications->count();

        // Pass all necessary data to the view
        return view('mahasiswa.mahasiswa_perwalian', compact(
            'notifications',
            'notificationCount',
            'student',
            'absensi',
            'dosenNotifications'
        ));
    }
}