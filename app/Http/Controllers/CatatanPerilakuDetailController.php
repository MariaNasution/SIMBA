<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrangTua;
use App\Models\Notifikasi;
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
        // Validasi input
        $validated = $request->validate([
            'student_nim' => 'required',
            'ta' => 'required',
            'semester' => 'required|integer',
            'type' => 'required|in:pelanggaran,perbuatan_baik',
            'pelanggaran' => 'nullable|string',
            'perbuatan_baik' => 'nullable|string',
            'unit' => 'nullable|string',
            'tanggal' => 'nullable|date',
            'poin' => 'nullable|integer',
            'tindakan' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'kredit_poin' => 'nullable|integer',
        ]);

        // Siapkan data untuk disimpan
        $data = [
            'student_nim' => $validated['student_nim'],
            'ta' => $validated['ta'],
            'semester' => $validated['semester'],
            'type' => $validated['type'],
            'unit' => $validated['unit'] ?? null,
            'tanggal' => $validated['tanggal'] ?? null,
            'poin' => $validated['poin'] ?? 0,
            'tindakan' => $validated['tindakan'] ?? null,
        ];

        // Simpan input ke kolom description sesuai tipe
        if ($validated['type'] === 'pelanggaran') {
            $data['description'] = $validated['pelanggaran'] ?? null;
        } else {
            $data['description'] = $validated['perbuatan_baik'] ?? null;
        }

        // Buat record StudentBehavior
        StudentBehavior::create($data);

        // --- Kirim SMS ke Orang Tua dan buat log ---
        $orangTua = OrangTua::where('nim', $validated['student_nim'])->first();
        if ($orangTua && $orangTua->no_hp) {
            $behaviorType = $validated['type'] === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik';
            $message = "Halo, data {$behaviorType} untuk mahasiswa dengan NIM {$validated['student_nim']} telah ditambahkan.";
            try {
                app(TwilioService::class)->sendSms($orangTua->no_hp, $message);
                Log::info("SMS berhasil dikirim ke {$orangTua->no_hp} untuk mahasiswa NIM {$validated['student_nim']}.");
            } catch (\Exception $e) {
                Log::error("Gagal mengirim SMS ke {$orangTua->no_hp} untuk mahasiswa NIM {$validated['student_nim']}. Error: " . $e->getMessage());
            }
        } else {
            Log::warning("Tidak ditemukan data Orang Tua atau nomor HP kosong untuk mahasiswa NIM {$validated['student_nim']}.");
        }

        // --- Buat notifikasi untuk mahasiswa ---
        $notificationMessage = "Data " . ($validated['type'] === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik') . " telah ditambahkan.";
        try {
            Notifikasi::create([
                'Pesan' => $notificationMessage,
                'nim' => $validated['student_nim'],
                'Id_Perwalian' => null, // karena notifikasi ini untuk catatan perilaku
            ]);
            Log::info("Notifikasi (tambah) berhasil dibuat untuk mahasiswa NIM {$validated['student_nim']}.");
        } catch (\Exception $e) {
            Log::error("Gagal membuat notifikasi untuk mahasiswa NIM {$validated['student_nim']}. Error: " . $e->getMessage());
        }

        // Redirect dengan flash message
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

    // Method update untuk mengupdate data sesuai tipe catatan
    public function update(Request $request, $id)
    {
        $behavior = StudentBehavior::findOrFail($id);

        if ($behavior->type == 'pelanggaran') {
            $validated = $request->validate([
                'pelanggaran' => 'required|string',
                'unit' => 'nullable|string',
                'tanggal' => 'nullable|date',
                'poin' => 'nullable|integer',
                'tindakan' => 'nullable|string',
            ]);
            $data = [
                'description' => $validated['pelanggaran'],
                'unit' => $validated['unit'] ?? null,
                'tanggal' => $validated['tanggal'] ?? null,
                'poin' => $validated['poin'] ?? 0,
                'tindakan' => $validated['tindakan'] ?? null,
            ];
        } elseif ($behavior->type == 'perbuatan_baik') {
            $validated = $request->validate([
                'perbuatan_baik' => 'required|string',
                'keterangan' => 'nullable|string',
                'unit' => 'nullable|string',
                'tanggal' => 'nullable|date',
                'kredit_poin' => 'nullable|integer',
                'tindakan' => 'nullable|string',
            ]);
            $data = [
                'description' => $validated['perbuatan_baik'],
                'unit' => $validated['unit'] ?? null,
                'tanggal' => $validated['tanggal'] ?? null,
                'poin' => $validated['kredit_poin'] ?? 0,
                'tindakan' => $validated['tindakan'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'kredit_poin' => $validated['kredit_poin'] ?? 0,
            ];
        }

        // Perbarui record
        $behavior->update($data);

        // --- Kirim SMS ke Orang Tua dan buat log (Update) ---
        $orangTua = OrangTua::where('nim', $behavior->student_nim)->first();
        if ($orangTua && $orangTua->no_hp) {
            $behaviorType = $behavior->type === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik';
            $message = "Halo, data {$behaviorType} untuk mahasiswa dengan NIM {$behavior->student_nim} telah diperbarui.";
            try {
                app(TwilioService::class)->sendSms($orangTua->no_hp, $message);
                Log::info("SMS update berhasil dikirim ke {$orangTua->no_hp} untuk mahasiswa NIM {$behavior->student_nim}.");
            } catch (\Exception $e) {
                Log::error("Gagal mengirim SMS update ke {$orangTua->no_hp} untuk mahasiswa NIM {$behavior->student_nim}. Error: " . $e->getMessage());
            }
        } else {
            Log::warning("Tidak ditemukan data Orang Tua atau nomor HP kosong untuk mahasiswa NIM {$behavior->student_nim} pada update data.");
        }

        // --- Buat notifikasi untuk mahasiswa (Update) ---
        $notificationMessage = "Data " . ($behavior->type === 'pelanggaran' ? 'Pelanggaran' : 'Perbuatan Baik') . " telah diperbarui.";
        try {
            Notifikasi::create([
                'Pesan' => $notificationMessage,
                'nim' => $behavior->student_nim,
                'Id_Perwalian' => null,
            ]);
            Log::info("Notifikasi (update) berhasil dibuat untuk mahasiswa NIM {$behavior->student_nim}.");
        } catch (\Exception $e) {
            Log::error("Gagal membuat notifikasi update untuk mahasiswa NIM {$behavior->student_nim}. Error: " . $e->getMessage());
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

        // Hapus record
        $behavior->delete();

        // --- Kirim SMS ke Orang Tua dan buat log (Hapus) ---
        $orangTua = OrangTua::where('nim', $studentNim)->first();
        if ($orangTua && $orangTua->no_hp) {
            $message = "Halo, data {$behaviorType} untuk mahasiswa dengan NIM {$studentNim} telah dihapus.";
            try {
                app(TwilioService::class)->sendSms($orangTua->no_hp, $message);
                Log::info("SMS hapus berhasil dikirim ke {$orangTua->no_hp} untuk mahasiswa NIM {$studentNim}.");
            } catch (\Exception $e) {
                Log::error("Gagal mengirim SMS hapus ke {$orangTua->no_hp} untuk mahasiswa NIM {$studentNim}. Error: " . $e->getMessage());
            }
        } else {
            Log::warning("Tidak ditemukan data Orang Tua atau nomor HP kosong untuk mahasiswa NIM {$studentNim} pada penghapusan data.");
        }

        // --- Buat notifikasi untuk mahasiswa (Hapus) ---
        $notificationMessage = "Data {$behaviorType} telah dihapus.";
        try {
            Notifikasi::create([
                'Pesan' => $notificationMessage,
                'nim' => $studentNim,
                'Id_Perwalian' => null,
            ]);
            Log::info("Notifikasi (hapus) berhasil dibuat untuk mahasiswa NIM {$studentNim}.");
        } catch (\Exception $e) {
            Log::error("Gagal membuat notifikasi hapus untuk mahasiswa NIM {$studentNim}. Error: " . $e->getMessage());
        }

        return redirect()
            ->route('catatan_perilaku_detail', ['studentNim' => $studentNim])
            ->with('success', 'Data berhasil dihapus.');
    }
}
