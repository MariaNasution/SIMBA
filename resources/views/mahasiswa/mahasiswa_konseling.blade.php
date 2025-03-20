@extends('layouts.app')

@section('content')

@php
  use Carbon\Carbon;
@endphp

<div class="d-flex align-items-center mb-4 border-bottom-line">
  <h3 class="me-auto">
    <a href="{{ route('beranda') }}"><i class="fas fa-code-branch me-3"></i>Home</a> /
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
<!-- Tabel Riwayat Konseling -->
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
            <td>{{ \Carbon\Carbon::parse($konseling->tanggal_pengajuan)->format('d M Y H:i') }}</td>
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
  {{ $konselings->links('pagination::bootstrap-4') }}
</div>

@endsection

