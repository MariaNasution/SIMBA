@extends('layouts.app')

@section('content')
  <div class="container">
    {{-- Header --}}
    <div class="d-flex align-items-center mb-4 border-bottom-line">
      <h3 class="me-auto">
        <a href="{{ route('admin') }}"><i class="fas fa-forward me-3"></i>Home</a> /
        <a href="{{ route('konseling_lanjutan') }}">Konseling Lanjutan</a>
      </h3>
      <a href="#" onclick="confirmLogout()">
        <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
      </a>
    </div>

    {{-- Judul --}}
    <h5 class="header-title text-primary mb-4">Mahasiswa Konseling Lanjutan</h5>

    {{-- Form Pencarian Mahasiswa --}}
    <form action="{{ route('konseling_lanjutan') }}" method="GET">
      <div class="col-md-6">
        <div class="mb-2 row">
          <label class="col-sm-2 col-form-label fw-bold">NIM</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="nim" value="{{ request('nim') }}" placeholder="">
          </div>
        </div>
        <div class="mb-2 row">
          <label class="col-sm-2 col-form-label fw-bold">Nama</label>
          <div class="col-sm-9">
            <input type="text" class="form-control" name="nama" value="{{ request('nama') }}" placeholder="">
          </div>
        </div>
      </div>

      {{-- Tombol --}}
      <div class="text-center mt-3">
        <button type="submit" class="btn btn-custom-blue">Cari</button>
        <a href="{{ route('konseling_lanjutan') }}" class="btn btn-secondary">Reset</a>
      </div>
    </form>

    {{-- Tabel Data Mahasiswa Konseling Lanjutan --}}
    @if ($mahasiswas->isNotEmpty())
      <div class="mt-4">
        <h4>Data Mahasiswa</h4>
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>NIM</th>
              <th>Nama</th>
            </tr>
          </thead>
          <tbody>
            @php
              // Group students by NIM and name to avoid duplicates
              $uniqueMahasiswa = $mahasiswas->unique(function($item) {
                return $item->nim . $item->nama;
              });
            @endphp
            @foreach($uniqueMahasiswa as $mahasiswa)
              <tr>
                <td>{{ $mahasiswa->nim }}</td>
                <td>
                  <a href="{{ route('konseling.lanjutan.detail', $mahasiswa->nim) }}">
                    {{ $mahasiswa->nama }}
                  </a>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <p class="text-center mt-4">Tidak ada data mahasiswa.</p>
    @endif

  </div>
@endsection