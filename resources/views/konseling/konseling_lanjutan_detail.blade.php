@extends('layouts.app')

@section('content')

    {{-- Informasi Mahasiswa --}}
    <div class="mb-4 text-start">
        <p><strong>Nama:</strong> {{ $nama }}</p>
        <p><strong>NIM:</strong> {{ $nim }}</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabel Hasil Konseling --}}
    <h5 class="text-start">Hasil Konseling:</h5>
    <p class="mt-3 text-end">
        Halaman <span class="fw-bold ">{{ $mahasiswas->currentPage() }}</span> dari
        <span class="fw-bold ">{{ $mahasiswas->lastPage() }}</span> |
        Menampilkan <span class="fw-bold ">{{$mahasiswas->count() }}</span> dari
        <span class="fw-bold">{{ $mahasiswas->total() }}</span> Entri data
    </p>
    @if ($mahasiswas->isNotEmpty())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Waktu</th>
                    <th>Hasil Konseling</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mahasiswas as $index => $konseling)
                    <tr>
                        <td>{{ ($mahasiswas->currentPage() - 1) * $mahasiswas->perPage() + $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($konseling->created_at)->translatedFormat('d F Y') }}</td>
                        <td>{{ $konseling->keterangan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center w-100 mt-3">
            {{ $mahasiswas->links('pagination::bootstrap-4') }}
        </div>
    @else
        <p class="text-muted">Belum ada hasil konseling.</p>
    @endif

@endsection