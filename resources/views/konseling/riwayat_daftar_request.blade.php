@extends('layouts.app')

@section('content')

  <div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
      @if(session('user.role') == 'kemahasiswaan')
        <a href="{{ route('kemahasiswaan_beranda') }}"> <i class="fas fa-clock me-3"></i>Konseling</a> /
        <a href="{{ route('kemahasiswaan_riwayat_daftar_request') }}">Riwayat Daftar Request</a>
      @elseif(session('user.role') == 'konselor')
        <a href="{{ route('konselor_beranda') }}"> <i class="fas fa-clock me-3"></i>Konseling</a> /
        <a href="{{ route('konselor_riwayat_daftar_request') }}">Riwayat Daftar Request</a>
      @endif
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
    {{-- Sorting Dropdown --}}
    <div class="d-flex justify-content-end mb-3">
      <p class="m-0">
        Halaman <span class="fw-bold">{{ $requests->currentPage() }}</span> dari
        <span class="fw-bold">{{ $requests->lastPage() }}</span> |
        Menampilkan <span class="fw-bold">{{ $requests->count() }}</span> dari
        <span class="fw-bold">{{ $requests->total() }}</span> Entri data
      </p>
    </div>
    <div class="d-flex justify-content-end mb-3">
      <form method="GET">
      <label for="sort" class="me-2">Urutkan:</label>
                    <select name="sort" id="sort" class="form-select w-auto d-inline" onchange="this.form.submit()">
                        <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                    </select>
      </form>
    </div>

    {{-- Tabel Data --}}
    <div class="table-responsive">
      <table id="hasilKonselingTable" class="table table-bordered text-center">
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
              <td>{{ \Carbon\Carbon::parse($request->tanggal_pengajuan)->format('d M Y, H:i') }}</td>
              <td>
                @if ($request->status == 'approved')
                  <span class="badge bg-success">Approved</span>
                @else
                  <span class="badge bg-danger">Rejected</span>
                @endif
              </td>
              <td>{{ $request->alasan_penolakan ?? '-' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-3">
      {{ $requests->appends(['sort' => request('sort')])->links('pagination::bootstrap-4') }}
    </div>
  </div>
@endsection
