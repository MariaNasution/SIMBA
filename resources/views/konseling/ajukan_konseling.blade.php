@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-2 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('admin') }}"> <i class="fas fa-user-friends me-3"></i>Home</a> /
            <a href="{{ route('daftar_pelanggaran') }}">Ajukan Konseling</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div class="container mt-2">
    <h4 class="header-title text-blue mb-4">Mahasiswa Aktif TA 2024</h4>

    <form>
        <div class="row mb-3">
            <label for="nim" class="col-sm-2 col-form-label fw-bold">NIM</label>
            <div class="col-sm-4">
                <input type="text" id="nim" class="form-control" placeholder="NIM">
            </div>
            <label for="prodi" class="col-sm-2 col-form-label fw-bold">Prodi</label>
            <div class="col-sm-4">
                <select id="prodi" class="form-select">
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
        <div class="row mb-3">
            <label for="nama" class="col-sm-2 col-form-label fw-bold">Nama</label>
            <div class="col-sm-4">
                <input type="text" id="nama" class="form-control" placeholder="Nama">
            </div>
            <label for="kelas" class="col-sm-2 col-form-label fw-bold">Kelas</label>
            <div class="col-sm-4">
                <input type="text" id="kelas" class="form-control" placeholder="Kelas">
            </div>
        </div>
        <div class="row mb-3">
            <label for="angkatan" class="col-sm-2 col-form-label fw-bold">Angkatan</label>
            <div class="col-sm-4">
                <select id="angkatan" class="form-select">
                    <option>2024</option>
                    <option>2023</option>
                    <option>2022</option>
                    <option>2021</option>
                    <option>2020</option>
                    <option>2019</option>
                </select>
            </div>
            <label for="wali" class="col-sm-2 col-form-label fw-bold">Wali</label>
            <div class="col-sm-4">
                <select id="wali" class="form-select">
                    <option selected>Wali</option>
                </select>
            </div>
        </div>
    </form>
</div>



          <div class="mt-5">
              <button class="btn btn-custom-blue">Cari</button>
              <button class="btn btn-secondary">Hapus</button>
          </div>


        <h5 class="mt-4 text-start">Waktu Konseling</h5>
          <div class="d-flex justify-content-start align-items-center">
              <input type="date" class="form-control flex-grow-1 date-input">
              <div class="ms-2">
                  <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                  <button class="btn btn-secondary btn-sm ms-1"><i class="fas fa-clock"></i></button>
              </div>
          </div>


          <div class="d-flex justify-content-center mt-4">
    <button class="btn btn-custom-blue btn-lg px-4 me-2">Buat</button>
    <button class="btn btn-secondary btn-lg px-4">Batal</button>
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
