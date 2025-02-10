@extends('layouts.app')

@section('content')
    <div class="app-wrapper px-4">
        <div class="d-flex align-items-center mb-4 border-bottom-line">
            <h3 class="me-auto">
                <a href="{{ route('beranda') }}">Home</a> /
                <a href="{{ route('profil') }}">Profil</a>
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>

        <div class="d-flex flex-column align-items-center">
            <!-- Foto Profil -->
            <div class="card text-center shadow-sm mb-4" style="width: 300px;">
                <img src="{{ asset('assets/img/profil.jpg') }}" alt="Profile Picture" class="rounded-circle mt-3 mx-auto"
                    style="width: 150px; height: 150px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title mb-0">{{ $student['nama'] ?? 'Nama Tidak Ditemukan' }}</h5>
                    <p class="text-muted">{{ $student['nim'] ?? 'NIM Tidak Ditemukan' }}</p>
                </div>
            </div>

            <!-- Kolom Data -->
            <div class="d-flex justify-content-between align-items-start flex-wrap w-100">
                <!-- Data Pribadi -->
                <div class="card shadow-sm p-3 m-2 flex-grow-1" style="max-width: 300px;">
                    <h5 class="card-title text-center mb-3">DATA PRIBADI</h5>
                    <ul class="list-unstyled">
                        <li><strong>Nama:</strong> {{ $student['nama'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>Email:</strong> {{ $student['email'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>HP:</strong> {{ $student['hp'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>Semester:</strong> {{ $student['sem'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>Prodi:</strong> {{ $student['prodi'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>Fakultas:</strong> {{ $student['fakultas'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>Asrama:</strong> {{ $student['asrama'] ?? 'Data Tidak Ditemukan' }}</li>

                    </ul>
                </div>

                <!-- Data Ayah -->
                <div class="card shadow-sm p-3 m-2 flex-grow-1" style="max-width: 300px;">
                    <h5 class="card-title text-center mb-3">DATA AYAH</h5>
                    <ul class="list-unstyled">
                        <li><strong>Nama Ayah:</strong> {{ $student['nama_ayah'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>No. HP Ayah:</strong> {{ $student['no_hp_ayah'] ?? 'Data Tidak Ditemukan' }}</li>
                    </ul>
                </div>

                <!-- Data Ibu -->
                <div class="card shadow-sm p-3 m-2 flex-grow-1" style="max-width: 300px;">
                    <h5 class="card-title text-center mb-3">DATA IBU</h5>
                    <ul class="list-unstyled">
                        <li><strong>Nama Ibu:</strong> {{ $student['nama_ibu'] ?? 'Data Tidak Ditemukan' }}</li>
                        <li><strong>No. HP Ibu:</strong> {{ $student['no_hp_ibu'] ?? 'Data Tidak Ditemukan' }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

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
                    window.location.href = '{{ route('logout') }}'; // Arahkan ke route logout jika 'Ya' dipilih
                }
            });
        }
    </script>
@endsection
