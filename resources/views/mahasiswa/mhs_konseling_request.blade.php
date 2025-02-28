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

  {{-- Form Request Konseling --}}
  <h3>Request Konseling</h3>
  <form action="{{ route('mhs_konseling_store') }}" method="POST">
    @csrf

    {{-- Waktu Konseling --}}
    <div class="mb-3 col-md-5">
      <label class="form-label text-start">Waktu Konseling</label>
      <div class="input-group">
        <input type="datetime-local" class="form-control" name="tanggal_pengajuan" id="tanggal_pengajuan" 
          value="{{ old('tanggal_pengajuan') }}" required>
        <button type="button" class="btn btn-danger btn-sm" onclick="resetTanggal()" title="Hapus Waktu">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    {{-- Keperluan Konseling --}}
    <div class="mb-3">
      <label class="form-label text-start">Keperluan Konseling</label>
      <textarea class="form-control" name="deskripsi_pengajuan" rows="10" required>{{ old('deskripsi_pengajuan') }}</textarea>
    </div>

    {{-- Tombol Submit dan Reset --}}
    <div class="text-start m-0">
    <button type="submit"class="btn btn-success me-2">Kirim</button>
    <button type="reset" class="btn btn-secondary">Reset</button>
     
    </div>

  </form>

  <script>
    function resetTanggal() {
      document.getElementById('tanggal_pengajuan').value = '';
    }

    document.addEventListener('DOMContentLoaded', function () {
      let inputTanggal = document.getElementById('tanggal_pengajuan');
      if (!inputTanggal.value) {
        let now = new Date();
        let dateTimeLocal = now.toISOString().slice(0, 16);
        inputTanggal.value = dateTimeLocal;
      }
    });
  </script>

@endsection
