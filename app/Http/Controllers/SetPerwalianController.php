<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perwalian;
use Illuminate\Support\Facades\Log;



class SetPerwalianController extends Controller
{
    public function index()
    {
        return view('perwalian.setPerwalian'); // Returns the view for the calendar page
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'selectedDate' => 'required|date|date_format:Y-m-d|after_or_equal:2025-02-01|before_or_equal:2025-06-30',
        ]);

        try {
            // Get the authenticated user's ID (assuming it's a dosen)
            $dosen = session('user'); // Use user ID as the primary identifier
            if (!$dosen) {
                throw new \Exception('Authenticated user ID is missing or invalid.');
            }

            // Create the perwalian record
            Perwalian::create([
                'date' => $validatedData['selectedDate'],
                'dosen_nip' => $dosen['nip'], // Use dosen_id to link to the dosen (user)
            ]);
            dd(Perwalian::All());
            // Log the successful creation
            Log::info('Perwalian date set for: ' . $validatedData['selectedDate'] . ' by dosen ID: ' . $dosen);
            // Redirect back with a success message
            return redirect()->route('set.perwalian')->with('success', 'Perwalian date set successfully for ' . $validatedData['selectedDate']);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to set Perwalian date: ' . $e->getMessage());

            // Redirect back with an error message
            return redirect()->route('set.perwalian')->with('error', 'Failed to set Perwalian date: ' . $e->getMessage());
        }
    }
}