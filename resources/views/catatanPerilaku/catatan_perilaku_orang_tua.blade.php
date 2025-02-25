@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 border-bottom">
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('catatan_perilaku') }}">Catatan Perilaku</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <!-- Konten Utama -->
    <div class="container mt-4">
        <h3 class="text-center mb-4">Daftar Nilai Perilaku Mahasiswa</h3>
        <hr class="mb-4">

        <!-- Tabel -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 5%">No</th>
                    <th>TA</th>
                    <th>Semester</th>
                    <th>Skor Awal</th>
                    <th>Akumulasi Skor</th>
                    <th>Nilai Huruf</th>
                </tr>
            </thead>
            <tbody>
                @php $index = 1; @endphp
                @forelse ($nilaiPerilaku as $key => $perilaku)
                    <tr>
                        <td style="width: 5%">{{ $index++ }}</td>
                        <td>{{ $perilaku['ta'] ?? '-' }}</td>
                        <td>{{ $perilaku['semester'] ?? '-' }}</td>
                        <td>{{ $perilaku['akumulasi_skor_awal'] ?? 0 }}</td>
                        <td>{{ $perilaku['akumulasi_skor'] ?? 0 }}</td>
                        <td>{{ $perilaku['nilai_huruf'] ?? '-' }}</td>
                        <td>
                            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse"
                                data-bs-target="#details{{ $key }}">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse" id="details{{ $key }}">
                        <td colspan="7">
                            <div class="p-3">
                                <h5>Pembinaan:</h5>
                                <ul class="nav nav-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" data-bs-toggle="tab"
                                            href="#pelanggaran{{ $key }}">Pelanggaran
                                            ({{ count($perilaku['pelanggaran']) }})
                                        </a>
                                    </li>
                                </ul>

                                <div class="tab-content mt-3">
                                    <!-- Tab Pelanggaran -->
                                    <div class="tab-pane fade show active" id="pelanggaran{{ $key }}">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Tanggal</th>
                                                    <th>Pelanggaran</th>
                                                    <th>Deskripsi</th>
                                                    <th>Bentuk</th>
                                                    <th>Tingkat</th>
                                                    <th>Poin</th>
                                                    <th>Pembinaan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $pelanggaranIndex = 1; @endphp
                                                @forelse ($perilaku['pelanggaran'] ?? [] as $pelanggaran)
                                                    <tr>
                                                        <td>{{ $pelanggaranIndex++ }}</td>
                                                        <td>{{ $pelanggaran['tanggal'] ?? '-' }}</td>
                                                        <td>{{ $pelanggaran['pelanggaran'] ?? '-' }}</td>
                                                        <td>{{ $pelanggaran['deskripsi'] ?? '-' }}</td>
                                                        <td>{{ $pelanggaran['bentuk'] ?? '-' }}</td>
                                                        <td>{{ $pelanggaran['tingkat'] ?? '-' }}</td>
                                                        <td>{{ $pelanggaran['poin'] ?? 0 }}</td>
                                                        <td>{{ $pelanggaran['pembinaan'] ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">Tidak ada data pelanggaran.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>

                                        </table>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Tidak ada data perilaku.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
