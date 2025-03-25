<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Dosen;
use App\Models\Dosen_Wali;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SetPerwalianController extends Controller
{
    public function index(Request $request)
    {
        $username = session('user')['username'] ?? null;
        $user = Dosen::where('username', $username)->first();

        // Fetch classes associated with the dosen from the dosen table
        $classes = [];
        if ($username) {
            $dosenRecord = DB::table('dosen_wali') // Adjust table name if different (e.g., 'dosen_wali')
                ->where('username', $username)
                ->first();
                // dd($dosenRecord);

            if ($dosenRecord && !empty($dosenRecord->kelas)) {
                $classes = array_map('trim', explode(',', $dosenRecord->kelas));
            }
        }

        // Check for an existing perwalian with Status = 'Scheduled' for this dosen
        $perwalianRequested = $user ? Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->where('Status', 'Scheduled')
            ->exists() : false;

        // Handle month navigation
        $month = $request->query('month', '2025-01'); // Default to January 2025
        $currentDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        // Restrict to January 2025 - June 2025
        if ($currentDate->lt(Carbon::create(2025, 1, 1))) {
            $currentDate = Carbon::create(2025, 1, 1);
        }
        if ($currentDate->gt(Carbon::create(2025, 6, 1))) {
            $currentDate = Carbon::create(2025, 6, 1);
        }

        // Fetch notifications
        $notifications = Notifikasi::with('perwalian')->get();

        // Fetch dosen data from API
        $apiToken = env('API_TOKEN');
        $dosenResponse = Http::withToken($apiToken)
            ->withOptions(['verify' => false])
            ->asForm()
            ->get('https://cis-dev.del.ac.id/api/library-api/dosen');

        $dosenData = $dosenResponse->json();

        // Filter dosen where their NIP matches ID_Dosen_Wali in notifications
        $dosenNotifications = collect();
        if ($dosenData && $notifications->isNotEmpty()) {
            $dosenWaliIds = $notifications->pluck('perwalian.ID_Dosen_Wali')->unique()->filter();
            $dosenNotifications = collect($dosenData)->whereIn('nip', $dosenWaliIds)->values();
        }

        return view('perwalian.setPerwalian', [
            'perwalian_requested' => $perwalianRequested,
            'dosenNotifications' => $dosenNotifications,
            'currentDate' => $currentDate,
            'classes' => $classes, // Pass the classes to the view
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'selectedDate' => 'required|date|date_format:Y-m-d|after_or_equal:1990-02-01|before_or_equal:2040-06-30',
            'selectedClass' => 'required|string', // Validate the selected class
        ]);

        try {
            $username = session('user')['username'] ?? null;
            $user = Dosen::where('username', $username)->first();

            if (!$user) {
                return redirect()->route('set.perwalian')->with('error', 'You must be logged in to set a perwalian date.');
            }

            // Check for existing perwalian with Status = 'Scheduled' for this class
            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
                ->where('kelas', $validatedData['selectedClass'])
                ->first();

            if ($existingPerwalian) {
                return redirect()->route('set.perwalian')->with('error', 'You already have a scheduled perwalian request for this class. Use the Edit option to delete and request again.');
            }

            // Create new perwalian
            $perwalian = Perwalian::create([
                'ID_Dosen_Wali' => $user->nip,
                'Tanggal' => Carbon::parse($validatedData['selectedDate'])->format('Y-m-d'),
                'Status' => 'Scheduled',
                'nama' => $user->nama,
                'kelas' => $validatedData['selectedClass'], // Save the selected class
            ]);

            if (!$perwalian) {
                Log::error('Failed to create Perwalian record');
                return redirect()->back()->withErrors(['perwalian' => 'Failed to create Perwalian record.']);
            }

            Log::info('Perwalian created:', ['perwalian' => $perwalian->toArray(), 'ID_Perwalian' => $perwalian->getKey()]);

            $nim = session('user')['nim'] ?? null;
            Notifikasi::create([
                'Pesan' => "Perwalian scheduled for " . $validatedData['selectedDate'] . " (Class: " . $validatedData['selectedClass'] . ")",
                'NIM' => $nim,
                'Id_Perwalian' => $perwalian->getKey(),
                'nama' => $user->nama,
            ]);

            Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] . ' by dosen NIP: ' . $user->nip . ' for class: ' . $validatedData['selectedClass']);

            return redirect()->route('set.perwalian')->with('success', 'Perwalian date set successfully for ' . $validatedData['selectedDate'] . ' (Class: ' . $validatedData['selectedClass'] . ')');
        } catch (\Exception $e) {
            Log::error('Failed to set Perwalian date: ' . $e->getMessage());
            return redirect()->route('set.perwalian')->with('error', 'Failed to set Perwalian date. Please try again.');
        }
    }

    public function destroy(Request $request)
    {
        try {
            $username = session('user')['username'] ?? null;
            $user = Dosen::where('username', $username)->first();

            if (!$user) {
                return redirect()->route('set.perwalian')->with('error', 'You must be logged in to delete a perwalian.');
            }

            // Delete the perwalian for the selected class
            $selectedClass = $request->input('selectedClass');
            if (!$selectedClass) {
                return redirect()->route('set.perwalian')->with('error', 'No class selected for deletion.');
            }

            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'Scheduled')
                ->where('kelas', $selectedClass)
                ->first();

            if ($existingPerwalian) {
                Notifikasi::where('Id_Perwalian', $existingPerwalian->getKey())->delete();
                $existingPerwalian->delete();
                Log::info('Perwalian deleted for dosen NIP: ' . $user->nip . ' for class: ' . $selectedClass);
            }

            return redirect()->route('set.perwalian')->with('success', 'Perwalian request deleted for class ' . $selectedClass . '. You can now create a new request.');
        } catch (\Exception $e) {
            Log::error('Failed to delete Perwalian: ' . $e->getMessage());
            return redirect()->route('set.perwalian')->with('error', 'Failed to delete Perwalian. Please try again.');
        }
    }
}