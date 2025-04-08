@extends('layouts.app')

@section('content')

    {{-- Alert Notifikasi --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card-body">
        {{-- Menampilkan jumlah data yang sedang ditampilkan --}}
        <p class="mt-3 text-end">
            Halaman <span class="fw-bold">{{ $requests->currentPage() }}</span> dari
            <span class="fw-bold">{{ $requests->lastPage() }}</span> |
            Menampilkan <span class="fw-bold">{{ $requests->count() }}</span> dari
            <span class="fw-bold">{{ $requests->total() }}</span> Entri data
        </p>

        {{-- Filter Sorting --}}
        <div class="d-flex justify-content-end mb-3">
            @if(session('user.role') == 'kemahasiswaan')
                <form action="{{ route('kemahasiswaan_daftar_request') }}" method="GET">
            @elseif(session('user.role') == 'konselor')
                <form action="{{ route('konselor_daftar_request') }}" method="GET">
            @endif
                    <label for="sort" class="me-2">Urutkan:</label>
                    <select name="sort" id="sort" class="form-select w-auto d-inline" onchange="this.form.submit()">
                        <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                        <option value="terlama" {{ request('sort') == 'terlama' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </form>
        </div>

        <table id="requestTable" class="table table-bordered">
            <thead class="table-secondary">
                <tr>
                    <th>No</th>
                    <th>NIM Mahasiswa</th>
                    <th>Nama Mahasiswa</th>
                    <th>Alasan Konseling</th>
                    <th>Waktu</th>
                    <th>Approve</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($requests as $key => $request)
                    <tr>
                        <td>{{ $requests->firstItem() + $key }}</td>
                        <td>{{ $request->nim }}</td>
                        <td>{{ $request->nama_mahasiswa }}</td>
                        <td>{{ $request->deskripsi_pengajuan }}</td>
                        <td>{{ $request->tanggal_pengajuan }}</td>
                        <td>
                            {{-- Approve Form --}}
                            @if(session('user.role') == 'kemahasiswaan')
                                <form action="{{ route('kemahasiswaan_approve_konseling', $request->id) }}" method="POST" class="d-inline">
                            @elseif(session('user.role') == 'konselor')
                                <form action="{{ route('konselor_approve_konseling', $request->id) }}" method="POST" class="d-inline">
                            @endif
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                {{-- Reject Button --}}
                                @if(session('user.role') == 'kemahasiswaan')
                                    <button class="btn btn-danger btn-sm reject-btn"
                                        data-url="{{ route('kemahasiswaan_reject_konseling', $request->id) }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @elseif(session('user.role') == 'konselor')
                                    <button class="btn btn-danger btn-sm reject-btn"
                                        data-url="{{ route('konselor_reject_konseling', $request->id) }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- Pagination --}}
        <div class="d-flex content-center mt-3">
            {{ $requests->links('pagination::bootstrap-4') }}
        </div>
    </div>

    <!-- Modal untuk alasan penolakan -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectReasonModalLabel">Masukkan Alasan Penolakan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="rejectReason" class="form-label">Alasan Penolakan:</label>
                            <textarea class="form-control" id="rejectReason" name="reject_reason" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const rejectButtons = document.querySelectorAll(".reject-btn");
            const rejectForm = document.getElementById("rejectForm");
            const rejectModal = new bootstrap.Modal(document.getElementById("rejectReasonModal"));

            rejectButtons.forEach(button => {
                button.addEventListener("click", function () {
                    const rejectUrl = this.getAttribute("data-url"); // Ambil URL dari button
                    rejectForm.setAttribute("action", rejectUrl); // Set action form ke URL yang sesuai
                    rejectModal.show(); // Tampilkan modal
                });
            });
        });
    </script>

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
    </script>

@endsection