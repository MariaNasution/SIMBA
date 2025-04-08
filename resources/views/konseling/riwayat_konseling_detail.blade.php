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

    <div>
        <h5 class="text-start">Hasil Konseling:</h5>

        {{-- Menampilkan jumlah data yang sedang ditampilkan --}}
        <p class="mt-3 text-end">
            Halaman <span class="fw-bold ">{{ $hasilKonseling->currentPage() }}</span> dari
            <span class="fw-bold">{{ $hasilKonseling->lastPage() }}</span> |
            Menampilkan <span class="fw-bold ">{{ $hasilKonseling->count() }}</span> dari
            <span class="fw-bold">{{ $hasilKonseling->total() }}</span> Entri data
        </p>
    </div>


    @if ($hasilKonseling->isNotEmpty())

        <table class="table table-bordered">
            <thead class="table-secondary text-center">
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
                                <td class="text-center">
                                    {{ ($hasilKonseling->currentPage() - 1) * $hasilKonseling->perPage() + $loop->iteration }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($konseling->created_at)->translatedFormat('d F Y') }}</td>
                                <td>{{ $konseling->keterangan }}</td>
                                <td class="text-center">
                                    @if ($konseling->file)
                                        <a href="{{ Storage::url('konseling_files/' . $konseling->file) }}" target="_blank">
                                            Lihat File
                                        </a>
                                    @else
                                        <span class="text-muted">No file found.</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($konseling->status == 'continued')
                                        <button type="button" class="btn btn-secondary btn-sm" disabled>
                                            <i class="fas fa-check"></i> Berhasil Dilanjutkan
                                        </button>
                                    @else
                                                        <form action="
                                            @if(session('user.role') == 'kemahasiswaan')
                                                {{ route('kemahasiswaan_konseling.lanjutan.store') }}
                                            @elseif(session('user.role') == 'konselor')
                                                {{ route('konselor_konseling.lanjutan.store') }}
                                            @endif
                                        " method="POST">
                                                            @csrf
                                                            <input type="hidden" name="nama" value="{{ $nama }}">
                                                            <input type="hidden" name="nim" value="{{ $nim }}">
                                                            <input type="hidden" name="request_konseling_id" value="{{ $konseling->id }}">
                                                            <button type="submit" class="btn btn-success btn-sm">
                                                                <i class="fas fa-check"></i> Lanjutkan
                                                            </button>
                                                        </form>

                                    @endif
                                </td>
                            </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination di tengah --}}
        <div class="d-flex justify-content-center w-100 mt-3">
            {{ $hasilKonseling->links('pagination::bootstrap-4') }}
        </div>
    @else
        <p class="text-muted">Belum ada hasil konseling.</p>
    @endif

@endsection