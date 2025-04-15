<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        // Fetch the latest perwalian record for the student
        $perwalian = Perwalian::where('ID_Perwalian', $student->ID_Perwalian)
            ->orderBy('updated_at', 'desc')
            ->first();

        // Fetch dosen info using the perwalian data (if any)
        $dosen = Dosen::where('nip', optional($perwalian)->ID_Dosen_Wali)->first();

        // Retrieve notifications using Laravel's built-in relationship.
        // If needed, you can filter by an extra key (for example, if you stored 'id_perwalian' in your notification payload).
        // Uncomment the following line if your perwalian notifications contain that key:
        // $notifications = $student->notifications()->where('data->id_perwalian', $student->ID_Perwalian)->orderBy('created_at', 'desc')->get();
        $notifications = $student->notifications()->orderBy('created_at', 'desc')->get();
        $notificationCount = $notifications->count();

        // (Optional) If your universal notifications include extra data such as an "ID_Dosen_Wali" key,
        // you can extract it to fetch a collection of dosen data related to notifications.
        $dosenWaliIds = $notifications->map(function ($notification) {
            return $notification->data['ID_Dosen_Wali'] ?? null;
        })->filter()->unique();

        $dosenNotifications = $dosenWaliIds->isNotEmpty() ? Dosen::whereIn('nip', $dosenWaliIds)->get() : collect();

        // Pass all necessary data to the view.
        return view('mahasiswa.mahasiswa_perwalian', compact(
            'student',
            'absensi',
            'perwalian', // ensure perwalian is included in the view
            'dosenNotifications',
            'notifications',
            'notificationCount',
            'dosen'
        ));
    }
}
