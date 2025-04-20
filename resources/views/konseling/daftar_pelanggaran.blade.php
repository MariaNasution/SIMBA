@extends('layouts.app')

@section('content')
    <div class="card-body">
        {{-- Form Pencarian Mahasiswa --}}
        <form action="{{ route(session('user.role') == 'kemahasiswaan' ? 'kemahasiswaan_pelanggaran.daftar' : 'konselor_pelanggaran.daftar') }}" 
              method="GET" 
              class="d-flex align-items-center justify-content-end mb-3">
            @csrf
            <input type="text" 
                   name="search" 
                   class="form-control me-2" 
                   style="max-width: 200px;" 
                   placeholder="Cari NIM atau Nama" 
                   value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary me-2">Cari</button>
            <a href="{{ route(session('user.role') == 'kemahasiswaan' ? 'kemahasiswaan_pelanggaran.daftar' : 'konselor_pelanggaran.daftar') }}" 
               class="btn btn-secondary">Reset</a>
        </form>

        {{-- Pesan Error --}}
        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Informasi Pagination --}}
        <p class="mt-3 text-end">
            Halaman <strong>{{ $pelanggaranList->currentPage() }}</strong> dari 
            <strong>{{ $pelanggaranList->lastPage() }}</strong> | 
            Menampilkan <strong>{{ $pelanggaranList->count() }}</strong> dari 
            <strong>{{ $pelanggaranList->total() }}</strong> entri
        </p>

        {{-- Tabel Data Pelanggaran --}}
        <table class="table table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th class="no-column">No</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Detail Pelanggaran</th>
                    <th>Jenis Pelanggaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggaranList as $data)
                    <tr>
                        <td class="no-column">
                            {{ ($pelanggaranList->currentPage() - 1) * $pelanggaranList->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $data['nim'] ?? '-' }}</td>
                        <td>{{ $data['nama'] ?? '-' }}</td>
                        <td>{{ $data['pelanggaran'] ?? '-' }}</td>
                        <td>{{ $data['tingkat'] ?? '-' }}</td>
                        <td>
                            <form action="{{ route(session('user.role') == 'kemahasiswaan' ? 'kemahasiswaan_konseling.pilih' : 'konselor_konseling.pilih') }}" 
                                  method="GET">
                                @csrf
                                <input type="hidden" name="nim" value="{{ $data['nim'] }}">
                                <input type="hidden" name="nama" value="{{ $data['nama'] }}">
                                <input type="hidden" name="tahun_masuk" value="{{ $data['tahun_masuk'] }}">
                                <input type="hidden" name="prodi" value="{{ $data['prodi'] }}">
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

        {{-- Pagination --}}
        @if($pelanggaranList->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $pelanggaranList->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@endsection