@extends('layouts.app') <!-- Layout utama -->

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 border-bottom">
        <!-- Link ke halaman Home -->
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('asrama') }}">Asrama</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <!-- Konten Utama -->
    <div class="container mt-4">
        <h3 class="text-center mb-4">Informasi Asrama</h3>
        <hr>

        <!-- Informasi Asrama -->
        <div class="row">
            <!-- Kolom Kiri -->
            <div class="col-md-6">
                <div class="equal-height">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>ASRAMA</th>
                                <td>{{ $asramaData['asrama'] ?? 'Tidak Diketahui' }}</td>
                            </tr>
                            <tr>
                                <th>PENGURUS ASRAMA</th>
                                <td>{{ $pembinaAsrama }}</td>
                            </tr>
                            <tr>
                                <th>KONTAK KEASRAMAAN</th>
                                <td>+62 812 1222 1189</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Kolom Kanan -->
            <div class="col-md-6">
                <div class="equal-height">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Kamar</th>
                                <td>Kamar {{ $asramaData['kamar'] ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Teman Sekamar</th>
                                <td>
                                    @if (!empty($asramaData['teman_sekamar']))
                                        @foreach ($asramaData['teman_sekamar'] as $teman)
                                            - {{ $teman['nama'] ?? '-' }} ({{ $teman['nim'] ?? '-' }}) <br>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Tidak ada teman sekamar.</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
