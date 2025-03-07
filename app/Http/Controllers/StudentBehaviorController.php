<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentBehavior;
use Illuminate\Support\Facades\Log;

class StudentBehaviorController extends Controller
{
    /**
     * Store a newly created record in storage (inline form).
     */
    public function store(Request $request)
    {
        // Validate required fields
        $validated = $request->validate([
            'student_nim' => 'required',
            'ta'          => 'required',
            'semester'    => 'required|integer',
            'type'        => 'required|in:pelanggaran,perbuatan_baik',

            // If you're storing "pelanggaran" in a single column (e.g., 'description'):
            'pelanggaran'       => 'nullable|string',
            'perbuatan_baik'    => 'nullable|string',

            // Common fields
            'unit'       => 'nullable|string',
            'tanggal'    => 'nullable|date',
            'poin'       => 'nullable|integer',
            'tindakan'   => 'nullable|string',

            // Additional fields if you have them:
            'keterangan'  => 'nullable|string',
            'kredit_poin' => 'nullable|integer',
        ]);

        // Prepare data for insertion
        // We'll map 'pelanggaran' or 'perbuatan_baik' into a single 'description' column
        $data = [
            'student_nim' => $validated['student_nim'],
            'ta'          => $validated['ta'],
            'semester'    => $validated['semester'],
            'type'        => $validated['type'],
            'unit'        => $validated['unit'] ?? null,
            'tanggal'     => $validated['tanggal'] ?? null,
            'poin'        => $validated['poin'] ?? 0,
            'tindakan'    => $validated['tindakan'] ?? null,
        ];

        // If it's pelanggaran, store 'pelanggaran' in 'description'
        if ($validated['type'] === 'pelanggaran') {
            $data['description'] = $validated['pelanggaran'] ?? null;
        }
        // If it's perbuatan_baik, store 'perbuatan_baik' in 'description'
        else {
            $data['description'] = $validated['perbuatan_baik'] ?? null;

            // If you want to store keterangan or kredit_poin separately:
            // $data['keterangan'] = $validated['keterangan'] ?? null;
            // $data['poin']       = $validated['kredit_poin'] ?? 0;
        }

        // Create the record
        StudentBehavior::create($data);

        // Redirect back to the detail page for the same student
        return redirect()
            ->route('catatan_perilaku_detail', ['studentNim' => $validated['student_nim']])
            ->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a record (if you prefer a separate page).
     */
    public function edit($id)
    {
        $behavior = StudentBehavior::findOrFail($id);
        return view('catatan_perilaku_detail', compact('behavior'));
    }

    /**
     * Update the specified record in storage.
     */
    public function update(Request $request, $id)
    {
        $behavior = StudentBehavior::findOrFail($id);

        $validated = $request->validate([
            'description' => 'nullable|string',
            'unit'        => 'nullable|string',
            'tanggal'     => 'nullable|date',
            'poin'        => 'nullable|integer',
            'tindakan'    => 'nullable|string',
            // Add more if needed
        ]);

        $behavior->update($validated);

        // Return to the same detail page
        return redirect()
            ->route('catatan_perilaku_detail', ['studentNim' => $behavior->student_nim])
            ->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Remove the specified record from storage.
     */
    public function destroy($id)
    {
        $behavior = StudentBehavior::findOrFail($id);
        $studentNim = $behavior->student_nim;  // Remember the student's NIM before deleting

        $behavior->delete();

        return redirect()
            ->route('catatan_perilaku_detail', ['studentNim' => $studentNim])
            ->with('success', 'Data berhasil dihapus.');
    }
}
