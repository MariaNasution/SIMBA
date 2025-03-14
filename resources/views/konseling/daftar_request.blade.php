
@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('admin') }}"> <i class="fas fa-list me-3"></i>Home</a> /
            <a href="{{ route('daftar_pelanggaran') }}">Daftar Request Konseling</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <thead class="table table-secondary">
                <tr>
                    <th class="no-column">No</th>
                    <th>NIM Mahasiswa</th>
                    <th>Nama Mahasiswa</th>
                    <th>Detail Pelanggaran</th>
                    <th>Jenis Pelanggaran</th>
                    <th>Ajukan</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($pelanggaranList) && count($pelanggaranList) > 0)
                    @foreach($pelanggaranList as $index => $pelanggaran)
                        <tr>
                            <td class="no-column">{{ $index + 1 }}</td>
                            <td>{{ $pelanggaran['nim'] ?? '-' }}</td>
                            <td>{{ $pelanggaran['nama'] ?? '-' }}</td>
                            <td>{{ $pelanggaran['pelanggaran'] ?? '-' }}</td>
                            <td>{{ $pelanggaran['tingkat'] ?? '-' }}</td>
                            <td>
                                <a href="{{ route('konseling_lanjutan') }}" class="btn btn-custom-blue">
                                    Ajukan Konseling
                                </a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data pelanggaran.</td>
                    </tr>
                @endif
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
                    window.location.href = '{{ route('logout') }}'; // Arahkan ke route logout jika 'Ya' dipilih
                }
            });
        }
    </script>
@endsection



