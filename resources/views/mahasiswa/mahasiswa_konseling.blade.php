@extends('layouts.app')

@section('content')

@php
  use Carbon\Carbon;
@endphp

<div class="d-flex align-items-center mb-4 border-bottom-line">
  <h3 class="me-auto">
    <a href="{{ route('beranda') }}"><i class="fas fa-user-friends me-3"></i>Home</a> /
    <a href="{{ route('mahasiswa_konseling') }}">Konseling</a>
  </h3>
  <a href="#" onclick="confirmLogout()">
    <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
  </a>
</div>

<!-- Tombol Request Konseling -->
<div class="mb-3 text-start">
  <a href="{{ route('mhs_konseling_request') }}"
     class="btn btn-success rounded-3 px-4 py-2 d-inline-flex align-items-center">
    <i class="fas fa-code-branch me-2"></i> Request Konseling
  </a>
</div>

<!-- Filter Status -->
<div class="container my-3">
  <div class="row">
    <div class="col-md-12 d-flex justify-content-end">
      <div class="dropdown">
        <button class="btn btn-light border dropdown-toggle d-flex align-items-center" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-funnel fas fa-filter"></i> <span id="selectedStatus">Filter Approve</span>
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li><a class="dropdown-item text-dark" href="#" onclick="setStatusFilter('Filter Approve', '')">Semua</a></li>
          <li><a class="dropdown-item text-primary" href="#" onclick="setStatusFilter('Menunggu Persetujuan', 'pending')">Menunggu Persetujuan</a></li>
          <li><a class="dropdown-item text-success" href="#" onclick="setStatusFilter('Disetujui', 'approved')">Disetujui</a></li>
          <li><a class="dropdown-item text-danger" href="#" onclick="setStatusFilter('Ditolak', 'rejected')">Ditolak</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

  <!-- Tabel Riwayat Daftar Request Konseling -->
<div class="table-responsive">
<table class="table table-bordered text-center">
    <thead class="table-light">
      <tr>
        <th>No</th>
        <th>Tanggal Pengajuan</th>
        <th>Keperluan Konseling</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
    @foreach ($konselings as $index => $konseling)
        <tr class="
            @if ($konseling->status == 'approved') table-success
            @elseif ($konseling->status == 'rejected') table-danger
            @elseif ($konseling->status == 'pending') table-primary
            @endif">
            <td>{{ ($konselings->currentPage() - 1) * $konselings->perPage() + $loop->iteration }}</td>
            <td>{{ Carbon::parse($konseling->tanggal_pengajuan)->translatedFormat('d M Y H:i') }}</td>
            <td>{{ $konseling->deskripsi_pengajuan }}</td>
            <td>
                @if ($konseling->status == 'approved')
                    ✅ Disetujui oleh Admin
                @elseif ($konseling->status == 'rejected')
                    ❌ Ditolak
                @else
                    ⏳ Menunggu Persetujuan Admin
                @endif
            </td>
        </tr>
    @endforeach
</tbody>
  </table>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center">
  {{ $konselings->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>

<!-- Script Filtering -->
<script>
  function setStatusFilter(text, value) {
    document.getElementById("selectedStatus").innerText = text;
    
    // Redirect dengan filter yang dipilih
    let url = new URL(window.location.href);
    if (value) {
      url.searchParams.set('status', value);
    } else {
      url.searchParams.delete('status');
    }
    window.location.href = url.toString();
  }

  // Saat halaman dimuat, atur teks tombol sesuai dengan filter yang aktif
  window.onload = function () {
    let urlParams = new URLSearchParams(window.location.search);
    let status = urlParams.get("status");
    if (status === "pending") {
      document.getElementById("selectedStatus").innerText = "Menunggu Persetujuan";
    } else if (status === "approved") {
      document.getElementById("selectedStatus").innerText = "Disetujui";
    } else if (status === "rejected") {
      document.getElementById("selectedStatus").innerText = "Ditolak";
    } else {
      document.getElementById("selectedStatus").innerText = "Filter Approve";
    }
  };
</script>

@endsection