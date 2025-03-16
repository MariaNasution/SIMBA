@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header dan Logout --}}
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('admin') }}"><i class="fas fa-history me-3"></i>Home</a> /
            <a href="{{ route('riwayat.konseling') }}">Riwayat Konseling</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    {{-- Informasi Mahasiswa --}}
    <div class="d-flex align-items-center mb-4">
        <div class="ms-3">
            <h5>{{ $mahasiswa->nama }}</h5>
            <p>NIM: {{ $mahasiswa->nim }}</p>
        </div>
    </div>

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
                                <a href="{{ asset('storage/konseling_files/' . $konseling->file) }}" target="_blank">
                                    {{ $konseling->file }}
                                </a>
                            @else
                                <span class="text-muted">no files found.</span>
                            @endif
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
