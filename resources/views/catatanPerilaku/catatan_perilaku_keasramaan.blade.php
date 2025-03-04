@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom">
        <h3 class="me-auto">
            <a href="#" onclick="showMainPage()">Beranda</a> /
            <a href="#" onclick="showMainPage()">Catatan Perilaku</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div id="main-content">
        <div class="container mt-4">
            <h3 class="text-center mb-4">Mahasiswa Aktif TA 2024</h3>

            <form method="GET" action="#" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Pencarian Mahasiswa">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th class="text-primary">NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th class="text-primary">Tahun Masuk</th>
                            <th>Program Studi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>11S22005</td>
                            <td><a href="#" onclick="showBehaviorLog()">Olga Frischila G.</a></td>
                            <td>2022</td>
                            <td>S1 Informatika</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="behavior-log" style="display: none;">
        <div class="container mt-4">
            <h3 class="text-center mb-4">Daftar Nilai Perilaku Mahasiswa</h3>
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>TA</th>
                        <th>Semester</th>
                        <th>Skor Awal</th>
                        <th>Akumulasi Skor</th>
                        <th>Nilai Huruf</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @php
                        $records = [
                            ['ta' => '2024 / 2025', 'semester' => 'Genap', 'skor_awal' => 0, 'akumulasi' => 0, 'nilai' => 'A'],
                            ['ta' => '2024 / 2025', 'semester' => 'Gasal', 'skor_awal' => 0, 'akumulasi' => 0, 'nilai' => 'A'],
                            ['ta' => '2023 / 2024', 'semester' => 'Pendek', 'skor_awal' => 0, 'akumulasi' => 0, 'nilai' => 'A'],
                        ];
                    @endphp
                    @foreach ($records as $index => $record)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $record['ta'] }}</td>
                            <td>{{ $record['semester'] }}</td>
                            <td>{{ $record['skor_awal'] }}</td>
                            <td>{{ $record['akumulasi'] }}</td>
                            <td>{{ $record['nilai'] }}</td>
                            <td>
                                <button class="btn btn-outline-primary" onclick="toggleDropdown('dropdown-{{ $index }}')">
                                    &#9660;
                                </button>
                            </td>
                        </tr>
                        <tr id="dropdown-{{ $index }}" class="d-none">
                            <td colspan="7" class="border-bottom">
                                <div class="p-3">
                                    <button class="btn btn-success" onclick="toggleForm('form-{{ $index }}')">Tambah</button>
                                    <div id="form-{{ $index }}" class="d-none mt-3">
                                        <h6>Tambah Poin Perilaku</h6>
                                        <div class="d-flex gap-2 mb-3">
                                            <input type="text" class="form-control" placeholder="Pelanggaran">
                                            <input type="text" class="form-control" value="Keasramaan" disabled>
                                            <input type="date" class="form-control">
                                            <input type="number" class="form-control" placeholder="Poin">
                                            <input type="text" class="form-control" placeholder="Tindakan">
                                            <button class="btn btn-success">Tambah</button>
                                        </div>
                                    </div>
                                    <h6>Pelanggaran</h6>
                                    <table class="table table-borderless">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Pelanggaran</th>
                                                <th>Unit</th>
                                                <th>Tanggal</th>
                                                <th>Poin</th>
                                                <th>Tindakan</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>Menyimpan peralatan kebersihan bermalam di kamar</td>
                                                <td>Keasramaan</td>
                                                <td>12 Feb 2025</td>
                                                <td>1</td>
                                                <td>Dicatat/Dinasehati/diberikan sanksi sesuai ketentuan pelanggaran</td>
                                                <td>
                                                    <button class="btn btn-outline-primary">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <h6>Perbuatan Baik</h6>
                                    <table class="table table-borderless">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Perbuatan Baik</th>
                                                <th>Keterangan</th>
                                                <th>Kredit Kebajikan Poin</th>
                                                <th>Unit</th>
                                                <th>Tanggal</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>Membantu kegiatan sosial kampus</td>
                                                <td>Aktif dalam bakti sosial</td>
                                                <td>2</td>
                                                <td>Sosial</td>
                                                <td>10 Feb 2025</td>
                                                <td>
                                                    <button class="btn btn-outline-primary">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <button class="btn btn-secondary" onclick="showMainPage()">Kembali</button>
        </div>
    </div>
    <script>
    function showBehaviorLog() {
        document.getElementById("main-content").style.display = "none";
        document.getElementById("behavior-log").style.display = "block";
    }

    function showMainPage() {
        document.getElementById("main-content").style.display = "block";
        document.getElementById("behavior-log").style.display = "none";
    }

    function toggleDropdown(id) {
        let element = document.getElementById(id);
        element.classList.toggle("d-none");
    }

    function toggleForm(id) {
        let element = document.getElementById(id);
        element.classList.toggle("d-none");
    }
</script>

@endsection
