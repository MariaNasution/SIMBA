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

    <div class="card-body">
        <table class="table table-bordered">
            <thead class="table table-secondary">
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
                <tr>
                    <td>1</td>
                    <td>11S22016</td>
                    <td>Fretty Debora Sirait</td>
                    <td>Sering sekali mempunyai kebiasaan malas</td>
                    <td>2025-02-16 12:00:00</td>
                    <td>
                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                        <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>11S22009</td>
                    <td>Dhea Simanjuntak</td>
                    <td>Sangat berlarut dalam kesedihan</td>
                    <td>2025-02-12 17:00:00</td>
                    <td>
                        <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                        <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
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
                    window.location.href = '{{ route('logout') }}';
                }
            });
        }
    </script>
@endsection
