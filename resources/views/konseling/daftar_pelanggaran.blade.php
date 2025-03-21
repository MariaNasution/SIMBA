@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('admin') }}"> <i class="fas fa-list me-3"></i>Home</a> /
            <a href="{{ route('daftar_pelanggaran') }}">Daftar Pelanggaran</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div class="card-body">
              {{-- Menampilkan jumlah data yang sedang ditampilkan --}}
              <p class="mt-3 text-end">
                Halaman <span class="fw-bold ">{{ $pelanggaranList->currentPage() }}</span> dari 
                <span class="fw-bold">{{ $pelanggaranList->lastPage() }}</span> | 
                Menampilkan <span class="fw-bold ">{{ $pelanggaranList->count() }}</span> dari
                <span class="fw-bold ">{{ $pelanggaranList->total() }}</span> Entri data
            </p>
        <table class="table table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th class="no-column">No</th>
                    <th>NIM Mahasiswa</th>
                    <th>Nama Mahasiswa</th>
                    <th>Detail Pelanggaran</th>
                    <th>Jenis Pelanggaran</th>
                    <th>Ajukan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggaranList as $dataMahasiswa)
                    <tr>
                        <td class="no-column">{{ ($pelanggaranList->currentPage() - 1) * $pelanggaranList->perPage() + $loop->iteration }}</td>
                        <td>{{ $dataMahasiswa['nim'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['nama'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['pelanggaran'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['tingkat'] ?? '-' }}</td>
                        <td>
                            <form action="{{ route('konseling.pilih') }}" method="GET">
                                @csrf
                                <input type="hidden" name="nim" value="{{ $dataMahasiswa['nim'] }}">
                                <input type="hidden" name="nama" value="{{ $dataMahasiswa['nama'] }}">
                                <input type="hidden" name="tahun_masuk" value="{{ $dataMahasiswa['tahun_masuk'] }}">
                                <input type="hidden" name="prodi" value="{{ $dataMahasiswa['prodi'] }}">
                                <button type="submit" class="btn btn-sm btn-primary">Ajukan Konseling</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data pelanggaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Tampilkan pagination hanya jika ada lebih dari 1 halaman --}}
        @if($pelanggaranList->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $pelanggaranList->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection
