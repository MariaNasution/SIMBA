<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestKonseling;
use App\Models\RiwayatDaftarRequestKonseling;

class DaftarRequestKonselingController extends Controller
{
  public function daftarRequest(Request $request)
  {
    // Ambil nilai filter sorting dari query string (default: terbaru)
    $sortOrder = $request->query('sort', 'terbaru');

    // Ambil request konseling dengan status pending dan sorting berdasarkan waktu request dibuat
    $requests = RequestKonseling::where('status', 'pending')
      ->orderBy('created_at', $sortOrder === 'terbaru' ? 'desc' : 'asc')
      ->paginate(7)
      ->appends(['sort' => $sortOrder]); // Menyimpan filter sorting saat berpindah halaman

    // Kirim data ke view
    return view('konseling.daftar_request', compact('requests', 'sortOrder'));
  }

  public function riwayatDaftarRequestKonseling(Request $request)
  {
    // Ambil nilai filter sorting dari query string (default: terbaru)
    $sortOrder = $request->query('sort', 'terbaru');

    // Ambil semua data dari tabel riwayat_daftar_request_konseling dengan sorting berdasarkan updated_at
    $requests = RiwayatDaftarRequestKonseling::orderBy('updated_at', $sortOrder === 'terbaru' ? 'desc' : 'asc')
      ->paginate(7)
      ->appends(['sort' => $sortOrder]); // Menyimpan filter sorting saat berpindah halaman

    // Kirim data ke view
    return view('konseling.riwayat_daftar_request', compact('requests', 'sortOrder'));
  }

  public function approve($id)
  {
    $request = RequestKonseling::findOrFail($id);

    // Pindahkan ke riwayat daftar request konseling
    RiwayatDaftarRequestKonseling::create([
      'nim' => $request->nim,
      'nama_mahasiswa' => $request->nama_mahasiswa,
      'tanggal_pengajuan' => $request->tanggal_pengajuan,
      'deskripsi_pengajuan' => $request->deskripsi_pengajuan,
      'alasan_penolakan' => '-',
      'status' => 'approved'
    ]);

    // Update status di tabel request_konseling
    $request->update(['status' => 'approved']);

    // Redirect berdasarkan role pengguna
    if (session('user.role') == 'kemahasiswaan') {
      return redirect()->route('kemahasiswaan_hasil_konseling')->with('success', 'Request berhasil disetujui.');
    } elseif (session('user.role') == 'konselor') {
      return redirect()->route('konselor_hasil_konseling')->with('success', 'Request berhasil disetujui.');
    }

    return redirect()->back()->with('success', 'Request berhasil disetujui.');
  }

  public function reject(Request $request, $id)
  {
    $request->validate([
      'reject_reason' => 'required|string|max:255',
    ]);

    $data = RequestKonseling::findOrFail($id);

    // Pindahkan ke riwayat daftar request konseling dengan alasan penolakan
    RiwayatDaftarRequestKonseling::create([
      'nim' => $data->nim,
      'nama_mahasiswa' => $data->nama_mahasiswa,
      'tanggal_pengajuan' => $data->tanggal_pengajuan,
      'deskripsi_pengajuan' => $data->deskripsi_pengajuan,
      'alasan_penolakan' => $request->reject_reason,
      'status' => 'rejected'
    ]);

    // Update status di tabel request_konseling menjadi rejected
    $data->update(['status' => 'rejected']);

    // Redirect berdasarkan role pengguna
    if (session('user.role') == 'kemahasiswaan') {
      return redirect()->route('kemahasiswaan_daftar_request')->with('error', 'Request berhasil ditolak.');
    } elseif (session('user.role') == 'konselor') {
      return redirect()->route('konselor_daftar_request')->with('error', 'Request berhasil ditolak.');
    }

    return redirect()->back()->with('error', 'Request berhasil ditolak.');
  }
}
