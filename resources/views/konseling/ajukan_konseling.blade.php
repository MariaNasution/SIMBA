@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Header dan Logout --}}
        <div class="d-flex align-items-center mb-4 border-bottom-line">
            <h3 class="me-auto">
                <a href="{{ route('admin') }}"><i class="fas fa-user-friends me-3"></i>Home</a> /
                <a href="{{ route('riwayat_konseling') }}">Ajukan Konseling</a>
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
                                <xoption>Teknik Komputer</xoption>
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

        <h6 class="mt-4 text-start">Waktu Konseling</h6>
        <div class="d-flex justify-content-start align-items-center">
            <input type="date" class="form-control flex-grow-1 date-input">
            <div>
                <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                <button class="btn btn-secondary btn-sm ms-1"><i class="fas fa-clock"></i></button>
            </div>
        </div>


        <div class="d-flex justify-content-center mt-4">
            <button class="btn btn-custom-blue btn-lg px-4 me-2">Buat</button>
            <button class="btn btn-secondary btn-lg px-4">Batal</button>
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