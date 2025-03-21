@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Header dan Logout --}}
        <div class="d-flex align-items-center mb-4 border-bottom-line">
            <h3 class="me-auto">
                @if(session('user.role') == 'kemahasiswaan')
                <a href="{{ route('kemahasiswaan') }}"> <i class="fas fa-list me-3"></i>Home</a> /
                <a href="{{ route('riwayat_konseling_kemahasiswaan') }}">Daftar Pelanggaran</a>
            @elseif(session('user.role') == 'konselor')
                <a href="{{ route('konselor') }}"> <i class="fas fa-list me-3"></i>Home</a> /
                <a href="{{ route('riwayat_konseling_konselor') }}">Daftar Pelanggaran</a>
            @endif
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>

        {{-- Judul --}}
        <h5 class="header-title text-primary mb-4">Mahasiswa Aktif TA 2024</h5>

        {{-- Form Pencarian Mahasiswa --}}
        <form action="{{ route('konseling.cari') }}" method="GET">
            @csrf
            <div class="col-md-6">
                <div class="mb-2 row">
                    <label class="col-sm-2 col-form-label fw-bold">NIM</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="nim" name="nim" value="{{ $nim ?? '' }}" required>
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
        {{-- Form untuk mengajukan konseling --}}
        <form action="{{ route('konseling.ajukan') }}" method="GET">
            @csrf
            <input type="hidden" name="nim" value="{{ $dataMahasiswa['nim'] ?? '' }}">

            {{-- Waktu Konseling --}}
            <div class="mb-3 col-md-5">
                <label class="form-label text-start d-block">Waktu Konseling</label>
                <div class="input-group">
                    <input type="datetime-local" class="form-control" name="tanggal_pengajuan" id="tanggal_pengajuan"
                        required>
                    <button type="button" class="btn btn-danger btn-sm" onclick="resetTanggal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            {{-- Tombol Konfirmasi --}}
            <div class="d-flex justify-content-center mt-4">
                <button type="submit" class="btn btn-custom-blue btn-lg px-4 me-2">Buat</button>
                <a href="{{ route('') }}" class="btn btn-secondary btn-lg px-4">Batal</a>
            </div>
        </form>
    </div>

    {{-- SweetAlert untuk Logout --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Apakah anda yakin ingin keluar?',
                text: "Anda akan keluar dari akun ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, keluar!',
                cancelButtonText: 'Tidak',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('logout') }}';
                }
            });
        }

        // Toggle time selector
        document.addEventListener('DOMContentLoaded', function () {
            const showTimeBtn = document.querySelector('.show-time');
            const timeSelector = document.querySelector('.time-selector');
            const clearDateBtn = document.querySelector('.clear-date');
            const dateInput = document.querySelector('.date-input');

            if (showTimeBtn) {
                showTimeBtn.addEventListener('click', function () {
                    timeSelector.style.display = timeSelector.style.display === 'none' ? 'block' : 'none';
                });
            }

            if (clearDateBtn) {
                clearDateBtn.addEventListener('click', function () {
                    dateInput.value = '';
                });
            }
            // Fungsi reset untuk tombol Hapus
            const resetButton = document.getElementById('resetButton');
            const nimInput = document.getElementById('nim');
            const mahasiswaData = document.getElementById('mahasiswaData');

            if (resetButton) {
                resetButton.addEventListener('click', function () {
                    // Reset form input
                    nimInput.value = '';

                    // Sembunyikan data mahasiswa jika ada
                    if (mahasiswaData) {
                        mahasiswaData.classList.add('d-none');
                    }

                    // Fokus kembali ke input NIM
                    nimInput.focus();
                });
            }
        });

        function resetTanggal() {
            document.getElementById('tanggal_pengajuan').value = '';
        }
    </script>
@endsection