<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrangTua;
use App\Services\TwilioService;
use App\Models\StudentBehavior;
use Illuminate\Support\Facades\Log;

class CatatanPerilakuDetailController extends Controller
{
    /**
     * Store a newly created record in storage (inline form).
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'student_nim'    => 'required',
            'ta'             => 'required',
            'semester'       => 'required|integer',
            'type'           => 'required|in:pelanggaran,perbuatan_baik',
            'pelanggaran'    => 'nullable|string',
            'perbuatan_baik' => 'nullable|string',
            'unit'           => 'nullable|string',
            'tanggal'        => 'nullable|date',
            'poin'           => 'nullable|integer',
            'tindakan'       => 'nullable|string',
            'keterangan'     => 'nullable|string',
            'kredit_poin'    => 'nullable|integer',
        ]);

        // Prepare data for saving
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

        // Save input to description field based on type
        if ($validated['type'] === 'pelanggaran') {
            $data['description'] = $validated['pelanggaran'] ?? null;
        } else {
            $data['description'] = $validated['perbuatan_baik'] ?? null;
        }

        // Create a new student behavior record
        // Observer akan mengirim notifikasi secara otomatis saat record dibuat.
        StudentBehavior::create($data);

        // --- Send SMS to Orang Tua and log ---
        $orangTua = OrangTua::where('nim', $validated['student_nim'])->first();
        if ($orangTua && $orangTua->no_hp) {
            $behaviorType = $validated['type'] === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik';
            $message = "Halo, data {$behaviorType} untuk mahasiswa dengan NIM {$validated['student_nim']} telah ditambahkan.";
            try {
                app(TwilioService::class)->sendSms($orangTua->no_hp, $message);
                Log::info("SMS sent to {$orangTua->no_hp} for mahasiswa NIM {$validated['student_nim']}.");
            } catch (\Exception $e) {
                Log::error("Failed to send SMS to {$orangTua->no_hp} for mahasiswa NIM {$validated['student_nim']}. Error: " . $e->getMessage());
            }
        } else {
            Log::warning("No Orang Tua record or phone number for mahasiswa NIM {$validated['student_nim']}.");
        }

        // Redirect with flash message
        return redirect()
            ->route('catatan_perilaku_detail', ['studentNim' => $validated['student_nim']])
            ->with('success', 'Data berhasil ditambahkan.');
    }

    // Method edit untuk menampilkan form edit
    public function edit($id)
    {
        $behavior = StudentBehavior::findOrFail($id);
        return view('catatan_perilaku_edit', compact('behavior'));
    }

    // Update method to update catatan perilaku record
    public function update(Request $request, $id)
    {
        $behavior = StudentBehavior::findOrFail($id);

        if ($behavior->type == 'pelanggaran') {
            $validated = $request->validate([
                'pelanggaran' => 'required|string',
                'unit'        => 'nullable|string',
                'tanggal'     => 'nullable|date',
                'poin'        => 'nullable|integer',
                'tindakan'    => 'nullable|string',
            ]);
            $data = [
                'description' => $validated['pelanggaran'],
                'unit'        => $validated['unit'] ?? null,
                'tanggal'     => $validated['tanggal'] ?? null,
                'poin'        => $validated['poin'] ?? 0,
                'tindakan'    => $validated['tindakan'] ?? null,
            ];
        } elseif ($behavior->type == 'perbuatan_baik') {
            $validated = $request->validate([
                'perbuatan_baik' => 'required|string',
                'keterangan'     => 'nullable|string',
                'unit'           => 'nullable|string',
                'tanggal'        => 'nullable|date',
                'kredit_poin'    => 'nullable|integer',
                'tindakan'       => 'nullable|string',
            ]);
            $data = [
                'description' => $validated['perbuatan_baik'],
                'unit'        => $validated['unit'] ?? null,
                'tanggal'     => $validated['tanggal'] ?? null,
                'poin'        => $validated['kredit_poin'] ?? 0,
                'tindakan'    => $validated['tindakan'] ?? null,
                'keterangan'  => $validated['keterangan'] ?? null,
                'kredit_poin' => $validated['kredit_poin'] ?? 0,
            ];
        }

        // Update record
        // Observer akan mengirim notifikasi secara otomatis saat record diupdate.
        $behavior->update($data);

        // --- Send SMS update to Orang Tua ---
        $orangTua = OrangTua::where('nim', $behavior->student_nim)->first();
        if ($orangTua && $orangTua->no_hp) {
            $behaviorType = $behavior->type === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik';
            $message = "Halo, data {$behaviorType} untuk mahasiswa dengan NIM {$behavior->student_nim} telah diperbarui.";
            try {
                app(TwilioService::class)->sendSms($orangTua->no_hp, $message);
                Log::info("SMS update sent to {$orangTua->no_hp} for mahasiswa NIM {$behavior->student_nim}.");
            } catch (\Exception $e) {
                Log::error("Failed to send SMS update to {$orangTua->no_hp} for mahasiswa NIM {$behavior->student_nim}. Error: " . $e->getMessage());
            }
        } else {
            Log::warning("No Orang Tua record or phone number for mahasiswa NIM {$behavior->student_nim} during update.");
        }

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
        $behaviorType = $behavior->type === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik';

        // Delete record
        // Observer akan mengirim notifikasi secara otomatis saat record dihapus.
        $behavior->delete();

        // --- Send SMS deletion notice to Orang Tua ---
        $orangTua = OrangTua::where('nim', $studentNim)->first();
        if ($orangTua && $orangTua->no_hp) {
            $message = "Halo, data {$behaviorType} untuk mahasiswa dengan NIM {$studentNim} telah dihapus.";
            try {
                app(TwilioService::class)->sendSms($orangTua->no_hp, $message);
                Log::info("SMS deletion sent to {$orangTua->no_hp} for mahasiswa NIM {$studentNim}.");
            } catch (\Exception $e) {
                Log::error("Failed to send SMS deletion to {$orangTua->no_hp} for mahasiswa NIM {$studentNim}. Error: " . $e->getMessage());
            }
        } else {
            Log::warning("No Orang Tua record or phone number for mahasiswa NIM {$studentNim} during deletion.");
        }

        return redirect()
            ->route('catatan_perilaku_detail', ['studentNim' => $studentNim])
            ->with('success', 'Data berhasil dihapus.');
    }
}
