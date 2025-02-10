@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <div class="d-flex align-items-center mb-4 border-bottom">
            <h3 class="me-auto">
                <a href="{{ route('beranda') }}">Home</a> /
                <a href="{{ route('absensi') }}">Perkuliahan</a> /
                <a href="{{ route('absensi') }}">Detail Absensi</a>
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>

        <div class="container mt-4">
            <h3 class="mb-4 fw-bold">Detail Absensi: {{ $course['nama'] }}</h3>
            <div class="table-responsive">
                <table class="table table-hover table-bordered text-center align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Nama Pengajar</th>
                            <th>Lokasi</th>
                            <th>Sesi</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($course['presensi'] as $index => $session)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $session['pengajar'] ?? '-' }}</td>
                                <td>{{ $session['lokasi'] }}</td>
                                <td>
                                    @if ($session['sesi'] === 'P')
                                        <span class="badge bg-success">Praktikum</span>
                                    @elseif ($session['sesi'] === 'T')
                                        <span class="badge bg-info">Teori</span>
                                    @else
                                        {{ $session['sesi'] }}
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($session['waktu_mulai'])->translatedFormat('d M Y H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($session['waktu_akhir'])->translatedFormat('d M Y H:i') }}</td>
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
