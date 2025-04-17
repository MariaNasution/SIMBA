@extends('layouts.app')

@section('content')

    {{-- Judul --}}
    <h5 class="header-title text-primary mb-4 text-start">Mahasiswa Aktif TA 2024</h5>

    {{-- Form Pencarian Mahasiswa --}}
    @if(session('user.role') == 'kemahasiswaan')
        <form action="{{ route('kemahasiswaan_riwayat.konseling.cari') }}" method="GET">
    @elseif(session('user.role') == 'konselor')
        <form action="{{ route('konselor_riwayat.konseling.cari') }}" method="GET">
    @endif
            @csrf
            <div class="col-md-6">
                <div class="mb-2 row">
                    <label class="col-sm-2 col-form-label fw-bold">NIM</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="nim" name="nim" value="{{ request('nim') }}">
                    </div>
                </div>
                <div class="mb-2 row">
                    <label class="col-sm-2 col-form-label fw-bold">Nama</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="nama" name="nama" value="{{ request('nama') }}">
                    </div>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="text-center mt-3">
                <button type="submit" class="btn btn-custom-blue">Cari</button>
                @if(session('user.role') == 'kemahasiswaan')
                    <a href="{{ route('kemahasiswaan_riwayat.konseling') }}" class="btn btn-secondary">Reset</a>
                @elseif(session('user.role') == 'konselor')
                    <a href="{{ route('konselor_riwayat.konseling') }}" class="btn btn-secondary">Reset</a>
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

        {{-- Tabel Data Mahasiswa --}}
        @if ($hasilKonseling->isNotEmpty())

            <div class="mt-4">
            <div class="text-start"><h4>Data Mahasiswa</h4></div>
                {{-- Menampilkan jumlah data yang sedang ditampilkan --}}
                <p class="mt-3 text-end">
                    Halaman <span class="fw-bold ">{{ $hasilKonseling->currentPage() }}</span> dari
                    <span class="fw-bold">{{ $hasilKonseling->lastPage() }}</span> |
                    Menampilkan <span class="fw-bold ">{{ $hasilKonseling->count() }}</span> dari
                    <span class="fw-bold ">{{ $hasilKonseling->total() }}</span> Entri data
                </p>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($hasilKonseling as $index => $mahasiswa)
                            <tr>
                                <td>{{ ($hasilKonseling->currentPage() - 1) * $hasilKonseling->perPage() + $loop->iteration }}</td>
                                <td>{{ $mahasiswa->nim }}</td>
                                <td>
                                    <a href="
                                            @if(session('user.role') == 'kemahasiswaan')
                                                {{ route('kemahasiswaan_riwayat.konseling.detail', $mahasiswa->nim) }}
                                            @elseif(session('user.role') == 'konselor')
                                                {{ route('konselor_riwayat.konseling.detail', $mahasiswa->nim) }}
                                            @endif
                                            ">
                                        {{ $mahasiswa->nama }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Tengah --}}
            <div class="d-flex justify-content-center w-100 mt-3">
                {{ $hasilKonseling->links('pagination::bootstrap-4') }}
            </div>
        @else
            <div class="alert alert-info mt-3">Tidak ada data mahasiswa yang ditemukan.</div>
        @endif
        
@endsection