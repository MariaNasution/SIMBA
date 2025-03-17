<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentBehavior;
use Illuminate\Support\Facades\Log;

class CatatanPerilakuDetailController extends Controller
{
    /**
     * Store a newly created record in storage (inline form).
     */
    public function store(Request $request)
    {
        // Validate required fields
        $validated = $request->validate([
            'student_nim'      => 'required',
            'ta'               => 'required',
            'semester'         => 'required|integer',
            'type'             => 'required|in:pelanggaran,perbuatan_baik',

            // Jika menyimpan "pelanggaran" di satu kolom (misalnya 'description'):
            'pelanggaran'      => 'nullable|string',
            'perbuatan_baik'   => 'nullable|string',

            // Common fields
            'unit'             => 'nullable|string',
            'tanggal'          => 'nullable|date',
            'poin'             => 'nullable|integer',
            'tindakan'         => 'nullable|string',

            // Additional fields jika ada:
            'keterangan'       => 'nullable|string',
            'kredit_poin'      => 'nullable|integer',
        ]);

        // Siapkan data untuk disimpan
        $data = [
            'student_nim'  => $validated['student_nim'],
            'ta'           => $validated['ta'],
            'semester'     => $validated['semester'],
            'type'         => $validated['type'],
            'unit'         => $validated['unit'] ?? null,
            'tanggal'      => $validated['tanggal'] ?? null,
            'poin'         => $validated['poin'] ?? 0,
            'tindakan'     => $validated['tindakan'] ?? null,
        ];

        // Simpan input ke kolom description sesuai tipe
        if ($validated['type'] === 'pelanggaran') {
            $data['description'] = $validated['pelanggaran'] ?? null;
        } else {
            $data['description'] = $validated['perbuatan_baik'] ?? null;
            // Jika ingin simpan keterangan atau kredit poin secara terpisah, bisa ditambahkan di sini.
        }

        // Buat record
        StudentBehavior::create($data);

        // Redirect dengan flash message
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
        ]);

        $behavior->update($validated);

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
        $studentNim = $behavior->student_nim;
        $behavior->delete();

        return redirect()
            ->route('catatan_perilaku_detail', ['studentNim' => $studentNim])
            ->with('success', 'Data berhasil dihapus.');
    }
}
