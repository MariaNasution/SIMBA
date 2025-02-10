@extends('layouts.app') <!-- Layout utama -->

@section('content')
    <!-- Breadcrumb dan Header -->
    <div class="d-flex align-items-center mb-4 border-bottom">
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('izin_bermalam') }}">Perizinan</a> /
            <a href="{{ route('izin_keluar') }}">Izin Keluar</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <!-- Konten Utama -->
    <div class="app-content-header">
        <div class="container-fluid">
            <h4 class="mb-4">Daftar Izin Keluar</h4>
            <!-- Menambahkan jumlah izin bermalam dengan format Showing 1-10 of x items -->
            @php
                $startItem = ($currentPage - 1) * $perPage + 1;
                $endItem = min($currentPage * $perPage, $total);
            @endphp
            <p>Showing {{ $startItem }}-{{ $endItem }} of {{ $total }} items</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Tabel Daftar Izin Keluar -->
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Tujuan Izin Keluar</th>
                        <th>Status Permohonan</th>
                        <th>Rencana Berangkat</th>
                        <th>Rencana Kembali</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($izinKeluar as $index => $izin)
                        <tr class="{{ $loop->index % 2 === 1 ? 'table-secondary' : '' }}">
                            <td>{{ $loop->iteration + ($currentPage - 1) * $perPage }}</td>
                            <td>{{ $izin['deskripsi'] ?? 'N/A' }}</td>
                            <td>{{ $izin['status_request'] ?? 'N/A' }}</td>
                            <td>{{ isset($izin['rencana_berangkat']) ? \Carbon\Carbon::parse($izin['rencana_berangkat'])->format('d M Y H:i') : 'N/A' }}
                            </td>
                            <td>{{ isset($izin['rencana_kembali']) ? \Carbon\Carbon::parse($izin['rencana_kembali'])->format('d M Y H:i') : 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Belum ada data izin keluar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if ($total > $perPage)
                <div class="d-flex justify-content-center mt-4">
                    <nav>
                        <ul class="pagination">
                            @for ($i = 1; $i <= ceil($total / $perPage); $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    <a class="page-link" href="?page={{ $i }}">{{ $i }}</a>
                                </li>
                            @endfor
                        </ul>
                    </nav>
                </div>
            @endif
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
