@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header dan Logout --}}
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('admin') }}"><i class="fas fa-history me-3"></i>Home</a> /
            <a href="{{ route('riwayat.konseling') }}">Riwayat Konseling</a> /
            <a href="#">Riwayat Konseling Detail</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    {{-- Informasi Mahasiswa --}}
    <div class="mb-4">
        <p>{{ $nama }}</p>
        <p>NIM: {{ $nim }}</p>
    </div>

    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    {{-- Tabel Hasil Konseling --}}
    <h5>Hasil Konseling:</h5>
    @if ($hasilKonseling->isNotEmpty())
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Waktu</th>
                <th>Hasil Konseling</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($hasilKonseling as $index => $konseling)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($konseling->created_at)->translatedFormat('d F Y') }}</td>
                <td>{{ $konseling->keterangan }}</td>
                <td>
                    @if ($konseling->file)
                    <a href="{{ Storage::url('konseling_files/' . $konseling->file) }}" target="_blank">
                        Lihat File
                    </a>
                    @else
                    <span class="text-muted">No file found.</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('konseling.lanjutan.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="nama" value="{{ $nama }}">
                        <input type="hidden" name="nim" value="{{ $nim }}">
                        <input type="hidden" name="konseling_id" value="{{ $konseling->id }}">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Lanjutkan
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p class="text-muted">Belum ada hasil konseling.</p>
    @endif
</div>
@endsection