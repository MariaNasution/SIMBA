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

        {{-- Judul --}}
        <h5 class="header-title text-primary mb-4">Mahasiswa Aktif TA 2024</h5>

        {{-- Form Pencarian Mahasiswa --}}
        <form action="{{ route('riwayat.konseling.cari') }}" method="GET">
            @csrf
            <div class="col-md-6">
                <div class="mb-2 row">
                    <label class="col-sm-2 col-form-label fw-bold">NIM</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="nim" name="nim" value="{{ $nim ?? '' }}">
                        </div>
                </div>
                <div class="mb-2 row">
                    <label class="col-sm-2 col-form-label fw-bold">Nama</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="nama" name="nama" value="{{ $nama ?? '' }}">
                        </div>
                </div>
            </div>
</br>
        {{-- Tombol --}}
        <div class="text-center">
            <button type="submit" class="btn btn-custom-blue">Cari</button>
            <button type="button" id="resetButton" class="btn btn-secondary">Hapus</button>
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
    @if (!empty($dataMahasiswa))
        <div class="mt-4">
            <h4>Data Mahasiswa</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Tahun Masuk</th>
                        <th>Program Studi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $dataMahasiswa['nim'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['nama'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['tahun_masuk'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['prodi'] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
        
    {{-- Tabel Mahasiswa --}}
        <div class="container">

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if (!empty($mahasiswas))
    <div class="mt-4">
        <h4>Daftar Mahasiswa</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Tahun Masuk</th>
                    <th>Program Studi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mahasiswas as $mahasiswa)
                    <tr>
                        <td>{{ $mahasiswa['nim'] }}</td>
                        <td>{{ $mahasiswa['nama'] }}</td>
                        <td>{{ $mahasiswa['angkatan'] }}</td>
                        <td>{{ $mahasiswa['prodi_name'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data mahasiswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif


        {{-- Pagination Dummy --}}
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">&laquo;</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">4</a></li>
                <li class="page-item"><a class="page-link" href="#">5</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>

    {{-- Logout Confirmation --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: "Anda yakin ingin keluar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('logout') }}';
                }
            });
        }
    </script>
@endsection