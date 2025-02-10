@extends('layouts.app') <!-- Layout utama -->

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <!-- Link ke halaman Home dan Bursar -->
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('bursar') }}">Bursar</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div class="d-flex mb-4 text-start">
        <div class="me-auto">
            <h4 class="mb-3">Tagihan Terbaru</h4>
            <!-- Card untuk Tagihan -->
            <div class="card border-0 shadow-sm" style="background-color: #50C2E3; max-width: 350px;">
                <div class="card-body text-center">
                    <h5 class="card-title mb-0 custom-title">Semua Tagihan Sudah Diproses</h5>
                </div>
            </div>
        </div>
        <h5 class="va-text mb-5">VA ( Bank {{ $VAData['bank'] ?? 'BNI' }} ) : {{ $VAData['virtual_account'] ?? null }}</h5>
    </div>

    <!-- Riwayat Tagihan -->
    <div class="card-header bg-white mt-5 mb-4">
        <h4 class="text-start"><i class="fas fa-history me-2"></i> History Tagihan</h4>
        <h5 class="text-start"><i class="fas fa-lock me-2"></i>Closed Payment VER: Verified, VWC: Verified With Credit, POS:
            Postponed, NEW: New Bill, REQ: Payment Requested, RPO: Req for Postpone, RCR: Req for Credit</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Periode</th>
                    <th>Nominal Minimum (Rp.)</th>
                    <th>Voucher (Rp.)</th>
                    <th>Total Nominal (Rp.)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bursarData as $index => $item)
                    <tr class="{{ $index % 2 === 1 ? 'table-secondary' : '' }}">
                        <td>{{ ($currentPage - 1) * $perPage + $index + 1 }}</td>
                        <td>{{ $item['periode'] ?? '-' }}</td>
                        <td>{{ number_format($item['nominal_minimum'] ?? 0, 2, ',', '.') }}</td>
                        <td>{{ number_format($item['voucher'] ?? 0, 2, ',', '.') }}</td>
                        <td>{{ number_format($item['total_nominal'] ?? 0, 2, ',', '.') }}</td>
                        <td>{{ $item['status'] ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('bursar.detail', $item['id']) }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-info-circle"></i> <small>Details</small>
                            </a>
                        </td>
                    </tr>

                    <!-- Modal untuk Detail -->
                    <div class="modal fade" id="detailModal{{ $index }}" tabindex="-1"
                        aria-labelledby="detailModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detailModalLabel">Detail Tagihan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Periode:</strong> {{ $item['periode'] ?? '-' }}</p>
                                    <p><strong>Tanggal Pembayaran:</strong> {{ $item['tanggal_pembayaran'] ?? '-' }}</p>
                                    <p><strong>Nominal Dibayarkan:</strong>
                                        {{ number_format($item['nominal_dibayarkan'] ?? 0, 2, ',', '.') }}</p>
                                    <h6>Detail Biaya:</h6>
                                    <ul>
                                        @forelse ($item['detail'] as $detail)
                                            <li>{{ $detail['nama_biaya'] ?? '-' }}: Rp.
                                                {{ number_format($detail['nominal_dibayarkan'] ?? 0, 2, ',', '.') }}</li>
                                        @empty
                                            <li class="text-muted">Tidak ada detail biaya.</li>
                                        @endforelse
                                    </ul>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada data bursar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            @php
                $totalPages = ceil($total / $perPage);
            @endphp
            <nav>
                <ul class="pagination">
                    @for ($page = 1; $page <= $totalPages; $page++)
                        <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        </li>
                    @endfor
                </ul>
            </nav>
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
