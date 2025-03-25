@extends('layouts.app')

@section('content')

    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('admin') }}"> <i class="fas fa-home me-3"></i>Home</a> /
            <a href="{{ route('daftar_request') }}">Daftar Request</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

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
<div class="d-flex justify-content-start mb-3">
  <form action="{{ route('daftar_request') }}" method="GET">
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
                            <form action="{{ route('approve_konseling', $request->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <button class="btn btn-success btn-sm">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <button class="btn btn-danger btn-sm reject-btn"
                                data-url="{{ route('reject_konseling', $request->id) }}">
                                <i class="fas fa-times"></i>
                            </button>
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

@endsection