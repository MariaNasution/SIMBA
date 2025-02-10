@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Judul Halaman -->
        <div class="d-flex align-items-center mb-4 border-bottom">
            <h3 class="me-auto">
                <a href="{{ route('beranda') }}">Home</a> /
                <a href="#">Perkuliahan</a> /
                <a href="{{ route('kemajuan_studi') }}">Kemajuan Studi</a> /
                <a href="#">Detail Nilai</a>
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>

        <!-- Informasi Mata Kuliah -->
        <h3 class="mb-3 underline-bold">Nilai Mahasiswa</h3>
        <div class="card-nilai p-3 shadow-sm mb-4">
            <table class="table table-bordered table-nilai">
                <tbody>
                    <tr>
                        <th>Kode Mata Kuliah</th>
                        <td>{{ $matkul['kode_mk'] }}</td>
                    </tr>
                    <tr>
                        <th>Nama Mata Kuliah</th>
                        <td>{{ $matkul['nama_kul_ind'] }}</td>
                    </tr>
                    <tr>
                        <th>Tahun Akademik</th>
                        <td>{{ $matkul['ta'] }}</td>
                    </tr>
                    <tr>
                        <th>Semester</th>
                        <td>{{ $penilaian['sem'] }}</td>
                    </tr>
                    <tr>
                        <th>Nilai Akhir</th>
                        <td>{{ $matkul['na'] }}</td>
                    </tr>
                    <tr>
                        <th>Grade</th>
                        <td>{{ $matkul['nilai'] }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <ul class="nav nav-tabs nav-tabs-nilai " id="nilaiTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="praktikum-tab" data-bs-toggle="tab" data-bs-target="#praktikum"
                    type="button" role="tab" aria-controls="praktikum" aria-selected="true">Praktikum</button>
            </li>

            <li class="nav-item">
                <button class="nav-link" id="tugas-tab" data-bs-toggle="tab" data-bs-target="#tugas" type="button"
                    role="tab" aria-controls="tugas" aria-selected="false">Tugas</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="kuis-tab" data-bs-toggle="tab" data-bs-target="#kuis" type="button"
                    role="tab" aria-controls="kuis" aria-selected="false">Kuis</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="uts-tab" data-bs-toggle="tab" data-bs-target="#uts" type="button"
                    role="tab" aria-controls="uts" aria-selected="false">UTS</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="uas-tab" data-bs-toggle="tab" data-bs-target="#uas" type="button"
                    role="tab" aria-controls="uas" aria-selected="false">UAS</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="tambahan-tab" data-bs-toggle="tab" data-bs-target="#tambahan" type="button"
                    role="tab" aria-controls="tambahan" aria-selected="false">Tambahan</button>
            </li>
        </ul>


        <div class="tab-content tab-content-nilai mt-4">
            <!-- Praktikum -->
            <div class="tab-pane fade show active" id="praktikum" role="tabpanel" aria-labelledby="praktikum-tab">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $detailNilai['nama_praktikum'] ?? 'Praktikum' }}</td>
                            <td>{{ $detailNilai['nilai_praktikum'] ?? 'Belum Ada Nilai' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Tugas -->
            <div class="tab-pane fade" id="tugas" role="tabpanel" aria-labelledby="tugas-tab">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $detailNilai['nama_tugas'] ?? 'Tugas' }}</td>
                            <td>{{ $detailNilai['nilai_tugas'] ?? 'Belum Ada Nilai' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Kuis -->
            <div class="tab-pane fade" id="kuis" role="tabpanel" aria-labelledby="kuis-tab">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $detailNilai['nama_kuis'] ?? 'Kuis' }}</td>
                            <td>{{ $detailNilai['nilai_kuis'] ?? 'Belum Ada Nilai' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- UTS -->
            <div class="tab-pane fade" id="uts" role="tabpanel" aria-labelledby="uts-tab">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $detailNilai['nama_uts'] ?? 'UTS' }}</td>
                            <td>{{ $detailNilai['nilai_uts'] ?? 'Belum Ada Nilai' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- UAS -->
            <div class="tab-pane fade" id="uas" role="tabpanel" aria-labelledby="uas-tab">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $detailNilai['nama_uas'] ?? 'UAS' }}</td>
                            <td>{{ $detailNilai['nilai_uas'] ?? 'Belum Ada Nilai' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Tambahan -->
            <div class="tab-pane fade" id="tambahan" role="tabpanel" aria-labelledby="tambahan-tab">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Nama Tambahan</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 1; $i <= 5; $i++)
                            @if (!empty($detailNilai['nilai_tambahan' . $i]))
                                <tr>
                                    <td>{{ $detailNilai['nama_tambahan' . $i] ?? 'Tambahan ' . $i }}</td>
                                    <td>{{ $detailNilai['nilai_tambahan' . $i] }}</td>
                                </tr>
                            @endif
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>


    </div>
@endsection
