@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Breadcrumb dan Header -->
        <div class="d-flex align-items-center mb-4 border-bottom">
            <h3 class="me-auto">
                <a href="{{ route('beranda') }}">Home</a> /
                <a href="{{ route('absensi') }}">Perkuliahan</a> /
                <a href="{{ route('profil') }}">Absensi Mahasiswa</a>
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>

        <!-- Judul Halaman -->
        <h3 class="fw-bold border-bottom pb-2">Absensi Mahasiswa</h3>

        <!-- Tabel -->
        <div class="mt-5">
            <table class="table table-hover table-bordered align-middle" style="width: 80%; margin: 0 auto;">
                <thead class="table-secondary">
                    <tr>
                        <th>#</th>
                        <th>Kode Matakuliah</th>
                        <th>Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Persentase Kehadiran (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $index => $attendance)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $attendance['kode_mk'] }}</td>
                            <td>
                                <a href="{{ route('absensi.detail', $attendance['kode_mk']) }}"
                                    class="text-decoration-none text-primary">
                                    {{ $attendance['nama_mk'] }}
                                </a>
                            </td>
                            <td>{{ $attendance['sks'] }}</td>
                            <td>{{ number_format($attendance['attendance_percentage'], 2) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
