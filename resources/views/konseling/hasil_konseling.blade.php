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

  <!-- Notifikasi -->
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif


  <form action="{{ route('hasil_konseling.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3 col-md-4">
    <label class="form-label text-start">Nama Mahasiswa</label>
    <input type="text" class="form-control" name="nama" required>
    </div>

    <div class="mb-3 col-md-4">
    <label class="form-label text-start">NIM Mahasiswa</label>
    <input type="text" class="form-control" name="nim" required>
    </div>

    <div class="mb-3">
    <label class="form-label text-start d-block">Hasil Konseling</label>
    <div id="upload-section" class="border p-3 bg-light d-flex flex-column">
      <!-- Tombol Upload File -->
      <button type="button" class="btn btn-custom-blue mb-2 align-self-end" onclick="showDropzone()">Upload
      File</button>

      <!-- Dropzone untuk upload file -->
      <div id="dropzone-section" class="d-none">
      <div class="border p-3 bg-light">
        <input type="file" class="form-control" name="file" required>
      </div>
      </div>

    </div>

    <table class="table table-bordered">
      <tbody>
      <tr>
        <td class="text-start">Keterangan Konseling:</td>
      </tr>
      <tr>
        <td>
        <textarea class="form-control" id="alasan" name="keterangan" rows="3"></textarea>
        </td>
      </tr>
      </tbody>
    </table>

    <div class="d-flex justify-content-center">
      <button type="submit" class="btn btn-custom-blue">Buat</button>
      <button type="reset" class="btn btn-secondary">Batal</button>
    </div>

  </form>

  <!-- Dropzone JS and CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

  <script>
    function showDropzone() {
    document.getElementById('dropzone-section').classList.remove('d-none');
    }

    // Inisialisasi Dropzone
    Dropzone.options.fileUpload = {
    paramName: 'file',
    maxFilesize: 5, // MB
    acceptedFiles: '.jpeg,.jpg,.png,.pdf,.doc,.docx',
    success: function (file, response) {
      document.getElementById('uploaded-file').classList.remove('d-none');
      document.getElementById('file-name').textContent = file.name;
    }
    };
  </script>

@endsection