@extends('layouts.app')

@section('content')

  <div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
    <a href="{{ route('admin') }}"> <i class="fas fa-clock me-3"></i>Home</a> /
    <a href="{{ route('riwayat_daftar_request') }}">Riwayat Daftar Request</a>
    </h3>
    <a href="#" onclick="confirmLogout()">
    <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
    </a>
  </div>

  {{-- Alert Notifikasi --}}
  @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <div class="card-body">
    <p class="mt-3 text-end">
    Halaman <span class="fw-bold">{{ $requests->currentPage() }}</span> dari
    <span class="fw-bold">{{ $requests->lastPage() }}</span> |
    Menampilkan <span class="fw-bold">{{ $requests->count() }}</span> dari
    <span class="fw-bold">{{ $requests->total() }}</span> Entri data
    </p>

    <table id="hasilKonselingTable" class="table table-bordered">
    <thead class="table-secondary">
      <tr>
      <th>No</th>
      <th>NIM Mahasiswa</th>
      <th>Nama Mahasiswa</th>
      <th>Alasan Konseling</th>
      <th>Waktu</th>
      <th>Status</th>
      <th>Alasan Penolakan</th>
      </tr>
    </thead>

    <tbody>
      @foreach ($requests as $key => $request)
      <tr>
      <td>{{ $requests->firstItem() + $key }}</td>
      <td>{{ $request->nim }}</td>
      <td>{{ $request->nama_mahasiswa }}</td>
      <td>{{ $request->deskripsi_pengajuan }}</td>
      <td>{{ $request->tanggal_pengajuan }}</td>
      <td>
      @if ($request->status == 'approved')
      <span class="badge bg-success">Approved</span>
    @else
      <span class="badge bg-danger">Rejected</span>
    @endif
      </td>
      <td>{{ $request->alasan_penolakan }}</td>
      </tr>
    @endforeach
    </tbody>
    </table>

    {{-- Pagination --}}
    <div class="d-flex content-center mt-3">
    {{ $requests->links('pagination::bootstrap-4') }}
    </div>
  </div>

@endsection