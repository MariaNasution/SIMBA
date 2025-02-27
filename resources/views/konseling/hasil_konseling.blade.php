@extends('layouts.app')

@section('content')

  <div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
    <a href="{{ route('admin') }}"><i class="fas fa-book me-3"></i>Home</a> /
    <a href="{{ route('hasil_konseling') }}">Hasil Konseling</a>
    </h3>
    <a href="#" onclick="confirmLogout()">
    <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
    </a>
  </div>


  <div class="mb-3 col-md-4">
    <label class="form-label text-start ">Nama Mahasiswa</label>
    <input type="text" class="form-control" id="nama_mahasiswa">
  </div>

  <div class="mb-3 col-md-4">
    <label class="form-label text-start ">NIM Mahasiswa</label>
    <input type="text" class="form-control" id="nim_mahasiswa">
  </div>


  <div class="mb-3">
    <label class="form-label text-start d-block">Hasil Konseling</label>
    <div id="upload-section" class="border p-3 bg-light d-flex flex-column">
    <!-- Tombol Upload File -->
    <button class="btn btn-custom-blue mb-2 align-self-end" onclick="showDropzone()">Upload File</button>

    <!-- Dropzone untuk upload file -->
    <div id="dropzone-section" class="d-none">
      <form action="{{ url('/upload-file') }}" class="dropzone" id="file-upload">
      @csrf
      </form>
    </div>

    <!-- Tampilkan nama file yang telah diupload -->
    <div id="uploaded-file" class="mt-2 d-none" style="max-width: 300px; font-size: 14px;">
      <strong>File:</strong> <span id="file-name"></span>
    </div>

    <table class="table table-bordered">
      <tbody>
      <tr>
        <td class="text-start">Keterangan Konseling:</td>
      </tr>
      <tr>
        <td>
        <textarea class="form-control" id="alasan" rows="3"></textarea>
        </td>
      </tr>
      </tbody>
    </table>

    </div>
  </div>

  <!-- Menjadikan tombol sejajar -->
  <div class="d-flex justify-content-center">
    <td><a href="{{ route('riwayat_konseling') }}" class="btn btn-custom-blue">Buat</a></td>
    <button class="btn btn-secondary">Batal</button>
  </div>

  <!-- SweetAlert & Dropzone -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css">

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

    function showDropzone() {
    document.getElementById('dropzone-section').classList.remove('d-none');
    }

    Dropzone.options.fileUpload = {
    paramName: "file",
    maxFilesize: 50, // MB
    acceptedFiles: ".pdf,.doc,.docx",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    },
    success: function (file, response) {
      Swal.fire("Berhasil!", "File berhasil diunggah.", "success");

      // Sembunyikan Dropzone setelah upload sukses
      document.getElementById('dropzone-section').classList.add('d-none');
      document.getElementById('uploaded-file').classList.remove('d-none');
      document.getElementById('file-name').textContent = file.name;

      // Masukkan nama file ke dalam textarea alasan
      document.getElementById('alasan').value = "File yang diunggah: " + file.name;
    },
    error: function () {
      Swal.fire("Gagal!", "File gagal diunggah.", "error");
    }
    };
  </script>

@endsection