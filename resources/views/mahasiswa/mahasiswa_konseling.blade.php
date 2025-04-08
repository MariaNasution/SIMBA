@extends('layouts.app')

@section('content')

@php
  use Carbon\Carbon;
@endphp



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
        <button class="btn btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
          <i class="fas fa-filter"></i> 
          <span>
            @if(request()->get('status') == 'pending')
              Menunggu Persetujuan
            @elseif(request()->get('status') == 'approved')
              Disetujui
            @elseif(request()->get('status') == 'rejected')
              Ditolak
            @else
              Filter Approve
            @endif
          </span>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('mahasiswa_konseling') }}">Semua</a></li>
          <li><a class="dropdown-item text-primary" href="{{ route('mahasiswa_konseling', ['status' => 'pending']) }}">Menunggu Persetujuan</a></li>
          <li><a class="dropdown-item text-success" href="{{ route('mahasiswa_konseling', ['status' => 'approved']) }}">Disetujui</a></li>
          <li><a class="dropdown-item text-danger" href="{{ route('mahasiswa_konseling', ['status' => 'rejected']) }}">Ditolak</a></li>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js">
  function setStatusFilter(text, value) {
    console.log("Filter clicked:", text, value); // Add this for debugging
    document.getElementById("selectedStatus").innerText = text;
    
    // Use window.location directly to avoid URL manipulation issues
    let currentUrl = window.location.origin + window.location.pathname;
    let newUrl = currentUrl;
    
    if (value && value !== '') {
      newUrl += '?status=' + value;
    }
    
    console.log("Redirecting to:", newUrl); // Debugging
    window.location.href = newUrl;
  }

  // When the page loads, set the button text according to the active filter
  document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get("status");
    
    if (status === "pending") {
      document.getElementById("selectedStatus").innerText = "Menunggu Persetujuan";
    } else if (status === "approved") {
      document.getElementById("selectedStatus").innerText = "Disetujui";
    } else if (status === "rejected") {
      document.getElementById("selectedStatus").innerText = "Ditolak";
    } else {
      document.getElementById("selectedStatus").innerText = "Filter Approve";
    }
  });
</script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
@endsection