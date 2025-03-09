<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use App\Models\Dosen;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SetPerwalianController extends Controller
{
    public function index()
    {
        return view('perwalian.setPerwalian');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'selectedDate' => 'required|date|date_format:Y-m-d|after_or_equal:1990-02-01|before_or_equal:2040-06-30',
        ]);

        try {
            // Get the authenticated user
            $username = session('user')['username'] ?? null;
            $user = Dosen::where('username', $username)->first();

            if (!$user) {
                return redirect()->route('set.perwalian')->with('error', 'You must be logged in to set a perwalian date.');
            }

            // Create the perwalian record
            $perwalian = Perwalian::create([
                'ID_Dosen_Wali' => $user->nip, // Matches dosen.nip, as per the updated migration
                'Tanggal' => Carbon::parse($validatedData['selectedDate'])->format('Y-m-d'),
                'Status' => 'requested',
            ]);

            // Check if Perwalian was created successfully
            if (!$perwalian) {
                Log::error('Failed to create Perwalian record');
                return redirect()->back()->withErrors(['perwalian' => 'Failed to create Perwalian record.']);
            }

            // Debug the Perwalian record to confirm the ID
            Log::info('Perwalian created:', ['perwalian' => $perwalian->toArray(), 'ID_Perwalian' => $perwalian->getKey()]);
            // dd($perwalian->toArray(), $perwalian->getKey()); // Uncomment to debug

            // Create the notifikasi record
            $nim = session('user')['nim'] ?? null; // Get NIM from session

            Notifikasi::create([
                'Pesan' => "Perwalian scheduled for " . $validatedData['selectedDate'],
                'NIM' => $nim,
                'Id_Perwalian' => $perwalian->getKey(), // Use getKey() to retrieve ID_Perwalian
            ]);
            

            // Log success
            Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] . ' by dosen NIP: ' . $user->nip);

            // Redirect with success message
            return redirect()->route('set.perwalian')->with('success', 'Perwalian date set successfully for ' . $validatedData['selectedDate']);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to set Perwalian date: ' . $e->getMessage());

            // Redirect with a user-friendly error message
            return redirect()->route('set.perwalian')->with('error', 'Failed to set Perwalian date. Please try again.');
        }
    }
}