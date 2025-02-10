@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('izin_bermalam') }}">Perizinan</a> /
            <a href="{{ route('izin_bermalam') }}">Izin Bermalam</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div class="app-content-header">
        <div class="container-fluid">
            <h4 class="mb-5">Daftar Izin Bermalam</h4>
            <!-- Menambahkan jumlah izin bermalam dengan format Showing 1-10 of x items -->
            @php
                $startItem = ($currentPage - 1) * $perPage + 1;
                $endItem = min($currentPage * $perPage, $total);
            @endphp
            <p>Showing {{ $startItem }}-{{ $endItem }} of {{ $total }} items</p>
        </div>
    </div>


    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Status Permohonan</th>
                    <th>Keperluan Izin Bermalam</th>
                    <th>Tujuan</th>
                    <th>Berangkat</th>
                    <th>Pulang</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($izinBermalam as $index => $izin)
                    <tr class="{{ $index % 2 === 1 ? 'table-secondary' : '' }}">
                        <td>{{ $loop->iteration + ($currentPage - 1) * $perPage }}</td>
                        <td>{{ $izin['status_request'] ?? 'N/A' }}</td>
                        <td>{{ $izin['deskripsi'] ?? 'N/A' }}</td>
                        <td>{{ $izin['tujuan'] ?? 'N/A' }}</td>
                        <td>
                            {{ isset($izin['rencana_berangkat']) ? \Carbon\Carbon::parse($izin['rencana_berangkat'])->format('d M Y H:i') : 'N/A' }}
                        </td>
                        <td>
                            {{ isset($izin['rencana_kembali']) ? \Carbon\Carbon::parse($izin['rencana_kembali'])->format('d M Y H:i') : 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada data izin bermalam.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        <nav>
            <ul class="pagination">
                @php
                    $totalPages = ceil($total / $perPage);
                @endphp

                <!-- Tombol Sebelumnya -->
                <li class="page-item {{ $currentPage == 1 ? 'disabled' : '' }}">
                    <a class="page-link" href="?page={{ $currentPage - 1 }}" aria-label="Sebelumnya">&laquo;</a>
                </li>

                <!-- Nomor Halaman -->
                @for ($page = 1; $page <= $totalPages; $page++)
                    <li class="page-item {{ $currentPage == $page ? 'active' : '' }}">
                        <a class="page-link" href="?page={{ $page }}">{{ $page }}</a>
                    </li>
                @endfor

                <!-- Tombol Berikutnya -->
                <li class="page-item {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                    <a class="page-link" href="?page={{ $currentPage + 1 }}" aria-label="Berikutnya">&raquo;</a>
                </li>
            </ul>
        </nav>
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
