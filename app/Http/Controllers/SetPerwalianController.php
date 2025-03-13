<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Dosen;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SetPerwalianController extends Controller
{
    public function index(Request $request)
    {
        $username = session('user')['username'] ?? null;
        $user = Dosen::where('username', $username)->first();

        // Check for an existing perwalian with Status = 'ongoing'
        $perwalianRequested = $user ? Perwalian::where('ID_Dosen_Wali', $user->nip)
            ->where('Status', 'ongoing')
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
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'selectedDate' => 'required|date|date_format:Y-m-d|after_or_equal:1990-02-01|before_or_equal:2040-06-30',
        ]);

        try {
            $username = session('user')['username'] ?? null;
            $user = Dosen::where('username', $username)->first();

            if (!$user) {
                return redirect()->route('set.perwalian')->with('error', 'You must be logged in to set a perwalian date.');
            }

            // Check for existing perwalian with Status = 'ongoing'
            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'ongoing')
                ->first();

            if ($existingPerwalian) {
                return redirect()->route('set.perwalian')->with('error', 'You can only have one ongoing perwalian request. Use the Edit option to delete and request again.');
            }

            // Create new perwalian
            $perwalian = Perwalian::create([
                'ID_Dosen_Wali' => $user->nip,
                'Tanggal' => Carbon::parse($validatedData['selectedDate'])->format('Y-m-d'),
                'Status' => 'ongoing',
            ]);

            if (!$perwalian) {
                Log::error('Failed to create Perwalian record');
                return redirect()->back()->withErrors(['perwalian' => 'Failed to create Perwalian record.']);
            }

            Log::info('Perwalian created:', ['perwalian' => $perwalian->toArray(), 'ID_Perwalian' => $perwalian->getKey()]);

            $nim = session('user')['nim'] ?? null;
            Notifikasi::create([
                'Pesan' => "Perwalian scheduled for " . $validatedData['selectedDate'],
                'NIM' => $nim,
                'Id_Perwalian' => $perwalian->getKey(),
            ]);

            Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] . ' by dosen NIP: ' . $user->nip);

            return redirect()->route('set.perwalian')->with('success', 'Perwalian date set successfully for ' . $validatedData['selectedDate']);
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

            $existingPerwalian = Perwalian::where('ID_Dosen_Wali', $user->nip)
                ->where('Status', 'ongoing')
                ->first();

            if ($existingPerwalian) {
                Notifikasi::where('Id_Perwalian', $existingPerwalian->getKey())->delete();
                $existingPerwalian->delete();
                Log::info('Perwalian deleted for dosen NIP: ' . $user->nip);
            }

            return redirect()->route('set.perwalian')->with('success', 'Perwalian request deleted. You can now create a new request.');
        } catch (\Exception $e) {
            Log::error('Failed to delete Perwalian: ' . $e->getMessage());
            return redirect()->route('set.perwalian')->with('error', 'Failed to delete Perwalian. Please try again.');
        }
    }
}