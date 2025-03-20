<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BeritaAcara;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Mahasiswa;

class BeritaAcaraController extends Controller
{
    public function index()
    {
        $userId = session('user');
        $berita_acaras = BeritaAcara::where('user_id', $userId)->get();
        $kelasTerbaru = session('kelasTerbaru') ?? ($berita_acaras->last()->kelas ?? null);
        $tanggalPerwalian = session('tanggalPerwalian') ?? ($berita_acaras->last()->tanggal_perwalian ?? null);
        return view('perwalian.berita_acara', compact('berita_acaras', 'kelasTerbaru', 'tanggalPerwalian'));
    }

    public function success($kelas, $tanggal_perwalian)
    {
        return view('perwalian.berita_acara_success', compact('kelas', 'tanggal_perwalian'));
    }


    public function store(Request $request)
    {

        $user = session('user');
        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Anda harus login untuk membuat berita acara.']);
        }
        $request->validate([
            'kelas' => 'required|string|max:255',
            'angkatan' => 'required|string|max:255',
            'tanggal_perwalian' => 'required|date_format:Y-m-d',
            'perihal_perwalian' => 'required|string|max:255',
            'agenda' => 'required|string',
            'hari_tanggal' => 'required|date_format:Y-m-d',
            'catatan' => 'nullable|string',
            'tanggal_ttd' => 'required|date_format:Y-m-d',
            'dosen_wali_ttd' => 'required|string|max:255',
        ]);

        // Insert data ke database
        $beritaAcara = BeritaAcara::create([
            'kelas' => $request->kelas,
            'angkatan' => $request->angkatan,
            'dosen_wali' => $user['username'],  // Ambil username dari session
            'tanggal_perwalian' => $request->tanggal_perwalian,
            'perihal_perwalian' => $request->perihal_perwalian,
            'agenda_perwalian' => $request->agenda, // Pastikan ini sesuai
            'hari_tanggal_feedback' => $request->hari_tanggal, // Sesuai dengan field di database
            'perihal_feedback' => $request->perihal2 ?? 'Tidak ada',
            'catatan_feedback' => $request->catatan, // Sesuai dengan database
            'tanggal_ttd' => $request->tanggal_ttd,
            'dosen_wali_ttd' => $request->dosen_wali_ttd,
            'user_id' => $user['user_id'],  // Pastikan user_id ada di database
        ]);

        session()->flash('kelasTerbaru', $request->kelas);
        session()->flash('tanggalPerwalian', $request->tanggal_perwalian);

        if ($beritaAcara) {
            return redirect()->route('berita-acara.success', [
                'kelas' => $request->kelas,
                'tanggal_perwalian' => $request->tanggal_perwalian,
            ]);
        } else {
            return back()->withErrors(['error' => 'Gagal menyimpan data']);
        }

    }

    public function show($id)
    {
        $beritaAcara = BeritaAcara::findOrFail($id);
        return view('perwalian.berita_acara_detail', compact('beritaAcara'));
    }


}
