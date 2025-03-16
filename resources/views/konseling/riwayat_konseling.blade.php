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
                <a href="{{ route('riwayat.konseling') }}" class="btn btn-secondary">Reset</a>
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
                <h4>Data Mahasiswa</h4>
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
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $mahasiswa->nim }}</td>
                                <td>
                                    <a href="{{ route('riwayat.konseling.detail', $mahasiswa->nim) }}">
                                        {{ $mahasiswa->nama }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        @else
            <div class="alert alert-info mt-3">Tidak ada data mahasiswa yang ditemukan.</div>
        @endif

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