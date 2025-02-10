@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('jadwal') }}">Perkuliahan</a> /
            <a href="{{ route('jadwal') }}">Jadwal</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <!-- Konten Utama -->
    <div class="app-content-header">
        <div class="container-fluid">
            <h4 class="mb-5">Jadwal Perkuliahan Mahasiswa</h4>
        </div>
    </div>

    <div class="card-body">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Jam</th>
                    @foreach ($jadwalFormatted['days'] as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($jadwalFormatted['times'] as $time)
                    <tr>
                        <td>{{ $time }}</td>
                        @foreach ($jadwalFormatted['days'] as $key => $day)
                            <td class="{{ !empty($jadwalFormatted['schedule'][$time][$key]) ? 'matkul' : '' }}">
                                @if (!empty($jadwalFormatted['schedule'][$time][$key]))
                                    @foreach ($jadwalFormatted['schedule'][$time][$key] as $matkul)
                                        <div>{{ $matkul }}</div>
                                    @endforeach
                                @else
                                    <!-- Jika kosong, kotak tetap tampil tapi tidak ada konten -->
                                    <div>&nbsp;</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
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
