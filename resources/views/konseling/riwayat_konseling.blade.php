@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Header dan Logout --}}
        <div class="d-flex align-items-center mb-4 border-bottom-line">
            <h3 class="me-auto">
                <a href="{{ route('admin') }}"><i class="fas fa-history me-3"></i>Home</a> /
                <a href="{{ route('riwayat_konseling') }}">Riwayat Konseling</a>
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>

        {{-- Judul --}}
        <h5 class="header-title text-primary mb-4">Mahasiswa Aktif TA 2024</h5>

        {{-- Form Pencarian --}}
        <form>
            <div class="row">
                {{-- Kolom Kiri --}}
                <div class="col-md-6">
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label fw-bold">NIM</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="NIM">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label fw-bold">Nama</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="Nama">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label fw-bold">Angkatan</label>
                        <div class="col-sm-9">
                            <select class="form-select">
                                <option>Angkatan</option>
                                @for ($i = 2019; $i <= 2024; $i++)
                                    <option>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="col-md-6">
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label fw-bold">Prodi</label>
                        <div class="col-sm-9">
                            <select class="form-select">
                                <option>Program Studi</option>
                                <option>Informatika</option>
                                <option>Sistem Informasi</option>
                                <option>Teknik Elektro</option>
                                <option>Teknologi Informasi</option>
                                <option>Teknik Komputer</option>
                                <option>Teknologi Rekayasa Perangkat Lunak</option>
                                <option>Manajemen Rekayasa</option>
                                <option>Metalurgi</option>
                                <option>Bioproses</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label fw-bold">Kelas</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="Kelas">
                        </div>
                    </div>
                    <div class="mb-2 row">
                        <label class="col-sm-2 col-form-label fw-bold">Wali</label>
                        <div class="col-sm-9">
                            <select class="form-select">
                                <option>Wali</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <br />
            {{-- Tombol --}}
            <div class="text-center">
                <button type="submit" class="btn btn-custom-blue">Cari</button>
                <button type="reset" class="btn btn-secondary">Hapus</button>
            </div>
        </form>


        {{-- Tabel Mahasiswa --}}
        <div class="table-responsive mt-4">
            <table class="table table-striped table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>#</th>
                        <th>NIM</th>
                        <th>Nama Mahasiswa</th>
                        <th>Tahun Masuk</th>
                        <th>Program Studi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $mahasiswa = [
                            ['nim' => '11S19027', 'nama' => 'Darel Deonaldo Aloysius Pinem', 'tahun' => '2019', 'prodi' => 'S1 Informatika'],
                            ['nim' => '11S19050', 'nama' => 'Risky Junior Martua Panggabean', 'tahun' => '2019', 'prodi' => 'S1 Informatika'],
                            ['nim' => '11S19055', 'nama' => 'Kartika Novia Hutauruk', 'tahun' => '2019', 'prodi' => 'S1 Informatika'],
                            ['nim' => '12S19036', 'nama' => 'Lucas Ronaldi Hutabarat', 'tahun' => '2019', 'prodi' => 'S1 Sistem Informasi'],
                            ['nim' => '14S19021', 'nama' => 'Albert Immanuel Sianipar', 'tahun' => '2019', 'prodi' => 'S1 Teknik Elektro'],
                            ['nim' => '14S19024', 'nama' => 'Jeffrey Jeverson Pasaribu', 'tahun' => '2019', 'prodi' => 'S1 Teknik Elektro'],
                            ['nim' => '14S19027', 'nama' => 'Marshal Pirhotson Lumbantobing', 'tahun' => '2019', 'prodi' => 'S1 Teknik Elektro'],
                            ['nim' => '14S19029', 'nama' => 'Herry John Peter', 'tahun' => '2019', 'prodi' => 'S1 Teknik Elektro'],
                            ['nim' => '11420012', 'nama' => 'Amiton Wanimbo', 'tahun' => '2020', 'prodi' => 'D IV Teknologi Rekayasa Perangkat Lunak'],
                            ['nim' => '11420020', 'nama' => 'Rohkid Kogoya', 'tahun' => '2020', 'prodi' => 'D IV Teknologi Rekayasa Perangkat Lunak'],
                        ];
                    @endphp
                    @foreach ($mahasiswa as $index => $mhs)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $mhs['nim'] }}</td>
                            <td>{{ $mhs['nama'] }}</td>
                            <td>{{ $mhs['tahun'] }}</td>
                            <td>{{ $mhs['prodi'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination Dummy --}}
        <nav>
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">&laquo;</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">4</a></li>
                <li class="page-item"><a class="page-link" href="#">5</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">&raquo;</a>
                </li>
            </ul>
        </nav>
    </div>

    {{-- Logout Confirmation --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: "Anda yakin ingin keluar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('logout') }}';
                }
            });
        }
    </script>
@endsection