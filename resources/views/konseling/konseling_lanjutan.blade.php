@extends('layouts.app')

@section('content')

  {{-- Judul --}}
  <h5 class="header-title text-primary mb-4 text-start">Mahasiswa Konseling Lanjutan</h5>

  {{-- Form Pencarian Mahasiswa --}}
  @if(session('user.role') == 'kemahasiswaan')
    <form action="{{ route('kemahasiswaan_konseling_lanjutan') }}" method="GET">
@elseif(session('user.role') == 'konselor')
  <form action="{{ route('konselor_konseling_lanjutan') }}" method="GET">
@endif
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

      @if(session('user.role') == 'kemahasiswaan')
      <a href="{{ route('kemahasiswaan_konseling_lanjutan') }}" class="btn btn-danger">Reset</a>
    @elseif(session('user.role') == 'konselor')
      <a href="{{ route('konselor_konseling_lanjutan') }}" class="btn btn-danger">Reset</a>
    @endif
    </div>

    </form>


    {{-- Menampilkan Error --}}
    @if ($errors->any())
    <div class="alert alert-danger mt-3">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
    @endforeach
    </ul>
    </div>
  @endif

    {{-- Tabel Data Mahasiswa Konseling Lanjutan --}}
    @if ($mahasiswas->isNotEmpty())
    <div class="mt-4">
    <div class="text-start"><h4>Data Mahasiswa</h4></div>
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
    $uniqueMahasiswa = $mahasiswas->unique(function ($item) {
    return $item->nim . $item->nama;
    });
  @endphp
      @foreach($uniqueMahasiswa as $mahasiswa)
      <tr>
      <td>{{ $mahasiswa->nim }}</td>
      <td>

      <a href="
      @if(session('user.role') == 'kemahasiswaan')
      {{ route('kemahasiswaan_konseling.lanjutan.detail', $mahasiswa->nim) }}
    @elseif(session('user.role') == 'konselor')
      {{ route('konselor_konseling.lanjutan.detail', $mahasiswa->nim) }}
  @endif
      ">
      {{ $mahasiswa->nama }}
      </a>

      </tr>
    @endforeach
      </tbody>
    </table>
    </div>

  @else
  <div class="alert alert-info mt-3">Tidak ada data mahasiswa yang ditemukan.</div>
@endif

  @endsection