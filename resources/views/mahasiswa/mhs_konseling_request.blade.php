@extends('layouts.app')

@section('content')

  <div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
    <a href="{{ route('beranda') }}">Home</a> /
    <a href="{{ route('mahasiswa_konseling') }}">Konseling</a> /
    <a href="{{ route('mhs_konseling_request') }}">Request Konseling</a>
    </h3>
    <a href="#" onclick="confirmLogout()">
    <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
    </a>
  </div>

  <h3>Request Konseling</h3>
  <br/>
  <form action="#" method="POST">
    @csrf
    <div class="mb-3">
      <label class="form-label text-start d-block">Nama</label>
      <input type="text" class="form-control" name="nama" required>
    </div>

    <div class="mb-3">
      <label class="form-label text-start d-block">NIM</label>
      <input type="text" class="form-control" name="nim" required>
    </div>

    <div class="mb-3">
      <label class="form-label text-start d-block">Tanggal Pengajuan</label>
      <input type="date" class="form-control" name="tanggal_pengajuan" required>
    </div>

    <div class="mb-3">
      <label class="form-label text-start d-block">Deskripsi</label>
      <textarea class="form-control" name="deskripsi_pengajuan" rows="3" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Submit Request</button>
  </form>

@endsection
