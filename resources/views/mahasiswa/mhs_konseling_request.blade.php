@extends('layouts.app')

@section('content')

  <div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
    <a href="{{ route('beranda') }}"><i class="fas fa-code-branch me-3"></i>Home</a> /
    <a href="{{ route('mhs_konseling_request') }}">Request Konseling</a>
    </h3>
    <a href="#" onclick="confirmLogout()">
    <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
    </a>
  </div>

  <h3>Request Konseling</h3>
  <br />
  <form action="#" method="POST">
    @csrf

    {{-- Waktu Konseling --}}
    <div class="mb-3 col-md-5">
    <label class="form-label text-start d-block">Waktu Konseling</label>
    <div class="input-group">
      <input type="datetime-local" class="form-control" name="tanggal_pengajuan" id="tanggal_pengajuan" required>
      <button type="button" class="btn btn-danger btn-sm" onclick="resetTanggal()">
      <i class="fas fa-times"></i>
      </button>
    </div>
    </div>

    {{-- Keperluan Konseling --}}
    <div class="mb-3">
    <label class="form-label text-start d-block">Keperluan Konseling</label>
    <textarea class="form-control" name="deskripsi_pengajuan" rows="10" required></textarea>
    </div>

    {{-- Tombol Submit dan Reset --}}
    <div class="d-flex justify-content-start">
    <button type="submit" class="btn btn-success">Buat</button>
    <button type="reset" class="btn btn-danger ms-2">Batal</button>
    </div>

  </form>

  <script>
    function resetTanggal() {
    document.getElementById('tanggal_pengajuan').value = '';
    }

    function showCurrentTime() {
    let now = new Date();
    let dateTimeLocal = now.toISOString().slice(0, 16);
    document.getElementById('tanggal_pengajuan').value = dateTimeLocal;
    }
  </script>

@endsection