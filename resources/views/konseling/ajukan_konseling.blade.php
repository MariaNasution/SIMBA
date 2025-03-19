@extends('layouts.app')

@section('content')
    
        {{-- Header dan Logout --}}
        <div class="d-flex align-items-center mb-4 border-bottom-line">
            <h3 class="me-auto">
                <a href="{{ route('admin') }}"><i class="fas fa-user-friends me-3"></i>Home</a> /
                <a href="{{ route('riwayat_konseling') }}">Ajukan Konseling</a>
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>
        
        {{-- Notifikasi Sukses/Error --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Judul --}}
        <h5 class="header-title text-primary mb-4 text-start">Mahasiswa Aktif TA 2024</h5>

        {{-- Form Pencarian Mahasiswa --}}
        <form action="{{ route('konseling.cari') }}" method="GET">
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
            </br>
            {{-- Tombol --}}
            <div class="text-center">
                <button type="submit" class="btn btn-custom-blue">Cari</button>
                <a href="{{ route('ajukan_konseling') }}" class="btn btn-secondary">Reset</a>
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

        {{-- Daftar Hasil Pencarian Mahasiswa --}}
        @if (!empty($daftarMahasiswa) && count($daftarMahasiswa) > 0)
            <div class="mt-4">
                <h4 class="text-start">Hasil Pencarian Mahasiswa</h4>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Tahun Masuk</th>
                            <th>Program Studi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daftarMahasiswa as $mahasiswa)
                            <tr>
                                <td>{{ $mahasiswa['nim'] ?? '-' }}</td>
                                <td>{{ $mahasiswa['nama'] ?? '-' }}</td>
                                <td>{{ $mahasiswa['tahun_masuk'] ?? '-' }}</td>
                                <td>{{ $mahasiswa['prodi'] ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('konseling.pilih') }}" method="GET">
                                        @csrf
                                        <input type="hidden" name="nim" value="{{ $mahasiswa['nim'] }}">
                                        <input type="hidden" name="nama" value="{{ $mahasiswa['nama'] }}">
                                        <input type="hidden" name="tahun_masuk" value="{{ $mahasiswa['tahun_masuk'] }}">
                                        <input type="hidden" name="prodi" value="{{ $mahasiswa['prodi'] }}">
                                        <button type="submit" class="btn btn-sm btn-primary">Pilih</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif(isset($daftarMahasiswa) && count($daftarMahasiswa) == 0)
            <div class="alert alert-info mt-3">
                Tidak ada mahasiswa yang ditemukan dengan kata kunci tersebut.
            </div>
        @endif

        {{-- Tampilkan data mahasiswa yang dipilih --}}
        @if (!empty($dataMahasiswa))
            <div class="mt-4">
                <h4 class="text-start">Data Mahasiswa yang Dipilih</h4>
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

            {{-- Form untuk mengajukan konseling --}}
            <form action="{{ route('konseling.ajukan') }}" method="POST">
                @csrf
                <input type="hidden" name="nim" value="{{ $dataMahasiswa['nim'] ?? '' }}">
                <input type="hidden" name="nama" value="{{ $dataMahasiswa['nama'] ?? '' }}">
                <input type="hidden" name="tahun_masuk" value="{{ $dataMahasiswa['tahun_masuk'] ?? '-' }}">
                <input type="hidden" name="prodi" value="{{ $dataMahasiswa['prodi'] ?? '-' }}">

                {{-- Waktu Konseling --}}
                <div class="mb-3 col-md-5">
                    <label class="form-label text-start d-block">Waktu Konseling</label>
                    <div class="input-group">
                        <input type="datetime-local" class="form-control" name="tanggal_pengajuan" id="tanggal_pengajuan"
                            min="{{ now()->format('Y-m-d\TH:i') }}" required>
                        <button type="button" class="btn btn-danger btn-sm" onclick="resetTanggal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                {{-- Tombol Konfirmasi --}}
                <div class="d-flex justify-content-center mt-4">
                    <button type="submit" class="btn btn-custom-blue btn-lg px-4 me-2">Buat</button>
                    <a href="{{ route('admin') }}" class="btn btn-secondary btn-lg px-4">Batal</a>
                </div>
            </form>
        @endif
    

    {{-- SweetAlert untuk Logout --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Fungsi reset untuk tombol Hapus
            const resetButton = document.getElementById('resetButton');
            const keywordInput = document.getElementById('keyword');

            if (resetButton) {
                resetButton.addEventListener('click', function () {
                    // Reset form input
                    keywordInput.value = '';

                    // Fokus kembali ke input keyword
                    keywordInput.focus();
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const tanggalPengajuan = document.getElementById('tanggal_pengajuan');

            function setMinDateTime() {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset()); // Sesuaikan dengan zona waktu lokal

                // Format datetime-local (YYYY-MM-DDTHH:MM)
                const minDateTime = now.toISOString().slice(0, 16);
                tanggalPengajuan.min = minDateTime;
            }

            setMinDateTime();

            // Mencegah pengguna memilih waktu yang sudah lewat
            tanggalPengajuan.addEventListener('input', function () {
                if (tanggalPengajuan.value < tanggalPengajuan.min) {
                    tanggalPengajuan.value = tanggalPengajuan.min;
                }
            });
        });

        function resetTanggal() {
            document.getElementById('tanggal_pengajuan').value = '';
        }
    </script>
@endsection