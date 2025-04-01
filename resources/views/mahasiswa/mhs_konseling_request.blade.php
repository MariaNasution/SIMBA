@extends('layouts.app')

@section('content')

  {{-- Header --}}
  <div class="d-flex align-items-center mb-4 border-bottom pb-2">
    <h3 class="me-auto">
    <a href="{{ route('beranda') }}"><i class="fas fa-home me-2"></i>Home</a> /
    <a href="{{ route('mhs_konseling_request') }}">Request Konseling</a>
    </h3>
    <a href="#" onclick="confirmLogout()" title="Logout">
    <i class="fas fa-sign-out-alt fs-5 cursor-pointer"></i>
    </a>
  </div>

  {{-- Notifikasi Sukses/Error --}}
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger">
    <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
  @endforeach
    </ul>
    </div>
  @endif

  {{-- Cek apakah session student_data tersedia --}}
  @php
    $user = session('student_data') ?? [];
    $nim = $user['nim'] ?? null;
    $namaMahasiswa = $user['nama'] ?? 'Nama tidak ditemukan';
  @endphp


  @if (!$nim)
    <div class="alert alert-warning">
    <strong>Perhatian:</strong> Data mahasiswa tidak ditemukan. Silakan hubungi administrator.
    </div>
  @else
    {{-- Form Request Konseling --}}
    <h3>Request Konseling</h3>
    <form action="{{ route('mhs_konseling_store') }}" method="POST">
    @csrf

    {{-- Menampilkan Informasi Mahasiswa --}}
    <div class="mb-3 col-md-4">
    <label class="form-label text-start">NIM</label>
    <input type="text" class="form-control" name="nim" value="{{ $nim }}" readonly>
    </div>

    <div class="mb-3 col-md-4">
    <label class="form-label text-start">Nama Mahasiswa</label>
    <input type="text" class="form-control" name="nama_mahasiswa" value="{{ $namaMahasiswa }}" readonly>
    </div>

    {{-- Waktu Konseling --}}
    <div class="mb-3 col-md-5">
    <label class="form-label text-start d-block">Waktu Konseling</label>
    <div class="input-group">
      <input type="datetime-local" class="form-control" name="tanggal_pengajuan" id="tanggal_pengajuan"
      min="{{ now()->format('Y-m-d\TH:i') }}" required>
      <button type="button" class="btn btn-danger btn-sm" onclick="resetTanggal()">
      <i class="fas fa-times"></i>
      </button>
    </div>
    </div>

    {{-- Keperluan Konseling --}}
    <div class="mb-3">
    <label class="form-label text-start">Keperluan Konseling</label>
    <textarea class="form-control" name="deskripsi_pengajuan" rows="10" required></textarea>
    </div>

    {{-- Tombol Submit --}}
    <div class="text-start m-0">
    <button type="submit" class="btn btn-success me-2">Buat</button>
    <button type="reset" class="btn btn-secondary">Batal</button>
    </div>
    </form>

  @endif

  {{-- Button Script --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
    // Fungsi reset untuk tombol Hapus
    const resetButton = document.getElementById('resetButton');
    const keywordInput = document.getElementById('keyword');

    if (resetButton) {
      resetButton.addEventListener('click', function () {
      // Reset form input
      keywordInput.value = '';

      // Fokus kembali ke input keyword
      keywordInput.focus();
      });
    }
    });

    document.addEventListener('DOMContentLoaded', function () {
    const tanggalPengajuan = document.getElementById('tanggal_pengajuan');

    function setMinDateTime() {
      const now = new Date();
      now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // Sesuaikan dengan zona waktu lokal

      // Format datetime-local (YYYY-MM-DDTHH:MM)
      const minDateTime = now.toISOString().slice(0, 16);
      tanggalPengajuan.min = minDateTime;
    }

    setMinDateTime();

    // Mencegah pengguna memilih waktu yang sudah lewat
    tanggalPengajuan.addEventListener('input', function () {
      if (tanggalPengajuan.value < tanggalPengajuan.min) {
      tanggalPengajuan.value = tanggalPengajuan.min;
      }
    });
    });

    function resetTanggal() {
    document.getElementById('tanggal_pengajuan').value = '';
    }
  </script>
@endsection