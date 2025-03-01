@extends('layouts.app')

@section('content')
  <div class="container">
    {{-- Header dan Logout --}}
    <div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
      <a href="{{ route('admin') }}"><i class="fas fa-history me-3"></i>Home</a> /
      <a href="{{ route('konseling_lanjutan') }}">Konseling Lanjutan</a>
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

    {{-- Tabel Data Mahasiswa --}}
    @if (!empty($dataMahasiswa))
    <div class="mt-4">
    <h4>Data Mahasiswa</h4>
    <table class="table table-bordered table-striped">
      <thead>
      <tr>
      <th>NIM</th>
      <th>Nama</th>
      <th>Tahun Masuk</th>
      <th>Program Studi</th>
      </tr>
      </thead>
      <tbody>
      <tr>
      <td>{{ $dataMahasiswa['nim'] ?? '-' }}</td>
      <td>{{ $dataMahasiswa['nama'] ?? '-' }}</td>
      <td>{{ $dataMahasiswa['tahun_masuk'] ?? '-' }}</td>
      <td>{{ $dataMahasiswa['prodi'] ?? '-' }}</td>
      </tr>
      </tbody>
    </table>
    </div>
  @endif

    {{-- Tabel Mahasiswa --}}
    <div class="container">

    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

    @if (!empty($mahasiswas))
    <div class="mt-4">
      <h4>Daftar Mahasiswa</h4>
      <table class="table table-bordered table-striped">
      <thead>
      <tr>
      <th>NIM</th>
      <th>Nama</th>
      <th>Tahun Masuk</th>
      <th>Program Studi</th>
      </tr>
      </thead>
      <tbody>
      @forelse($mahasiswas as $mahasiswa)
      <tr>
      <td>{{ $mahasiswa['nim'] }}</td>
      <td>{{ $mahasiswa['nama'] }}</td>
      <td>{{ $mahasiswa['angkatan'] }}</td>
      <td>{{ $mahasiswa['prodi_name'] }}</td>
      </tr>
    @empty
      <tr>
      <td colspan="4" class="text-center">Tidak ada data mahasiswa.</td>
      </tr>
    @endforelse
      </tbody>
      </table>
    </div>
  @endif

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