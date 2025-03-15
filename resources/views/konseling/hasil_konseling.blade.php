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

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @php
    use App\Models\RequestKonseling;
    $mahasiswa = RequestKonseling::where('status', 'approved')->get();
  @endphp

  <form action="{{ route('hasil_konseling.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="mb-3 col-md-4">
    <label class="form-label text-start">Nama Mahasiswa</label>
    <select name="nama" id="nama_mahasiswa" class="form-control" required>
        <option value="">Cari dan Pilih Mahasiswa</option>
        @foreach ($mahasiswa as $mhs)
            <option value="{{ $mhs->nama_mahasiswa }}" data-nim="{{ $mhs->nim }}" data-deskripsi="{{ $mhs->deskripsi_pengajuan }}">
                {{ $mhs->nama_mahasiswa }} - {{ $mhs->deskripsi_pengajuan }}
            </option>
        @endforeach
    </select>
    </div>

    <div class="mb-3 col-md-4">
        <label class="form-label text-start">NIM Mahasiswa</label>
        <input type="text" class="form-control" name="nim" id="nim_mahasiswa" required readonly>
    </div>

    <div class="mb-3">
    <label class="form-label text-start d-block">Hasil Konseling</label>
    <div id="upload-section" class="border p-3 bg-light d-flex flex-column">
      <button type="button" class="btn btn-custom-blue mb-2 align-self-end" onclick="showDropzone()">Upload
      File</button>
      <div id="dropzone-section" class="d-none">
      <div class="border p-3 bg-light">
        <input type="file" class="form-control" name="file" required>
      </div>
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
        <textarea class="form-control" name="keterangan" rows="3"></textarea>
      </td>
      </tr>
    </tbody>
    </table>

    <div class="d-flex justify-content-center">
    <button type="submit" class="btn btn-custom-blue">Buat</button>
    <button type="reset" class="btn btn-secondary">Batal</button>
    </div>
  </form>

  <!-- Script untuk Dropzone -->
  <script>
    function showDropzone() {
        document.getElementById('dropzone-section').classList.remove('d-none');
    }
  </script>

  <!-- Script untuk Select2 dan Auto-fill NIM serta Deskripsi -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

  <script>
  $(document).ready(function () {
        $('#nama_mahasiswa').select2({
            placeholder: "Cari Mahasiswa...",
            allowClear: true,
            width: '100%',
            templateSelection: function (data) {
                return data.text.split(' - ')[0];
            }
        });

        $('#nama_mahasiswa').on('change', function () {
            const selectedOption = $(this).find('option:selected');
            const nim = selectedOption.data('nim');
            $('#nim_mahasiswa').val(nim || '');
        });
    });
  </script>

  <style>
      /* Agar teks yang dipilih di Select2 tetap rata kiri */
      .select2-container .select2-selection--single .select2-selection__rendered {
          text-align: left !important;
      }
  </style>

@endsection
