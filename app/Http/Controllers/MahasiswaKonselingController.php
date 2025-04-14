<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestKonseling;
use App\Models\Mahasiswa;

class MahasiswaKonselingController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data mahasiswa dari session
        $apiToken = session('api_token');

        // Fetch student data from session
        $student = Mahasiswa::where('nim', session('user')['nim'] ?? null)->first();

        // Redirect if student data is not found
        if (!$student) {
            return redirect()->back()->with('error', 'Data mahasiswa tidak ditemukan.');
        }

        // Fetch notifications for the student
        $notifications = $student->notifications()->orderBy('created_at', 'desc')->get();
        $notificationCount = $notifications->count();

        // Fetch konseling requests with optional status filter
        $status = $request->query('status', '');
        $query = RequestKonseling::where('nim', $student->nim)->orderBy('tanggal_pengajuan', 'desc');

         if (!empty($status)) {
            $query->where('status', $status);
        }

        $konselings = $query->paginate(10)->appends(['status' => $status]);

        // Pass all necessary data to the view
        return view('mahasiswa.mahasiswa_konseling', compact(
            'konselings',
            'notifications',
            'notificationCount'
        ));
    }
}
