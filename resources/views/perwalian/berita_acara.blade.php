@extends('layouts.app')

@section('content')
  <div class="container text-center">
    <!-- Logo Kampus -->
    <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4"
    style="max-width: 150px;">

    <!-- Judul Halaman -->
    <h3 class="fw-bold title-centered">AGENDA PERWALIAN</h3>

    <form action="{{ route('berita_acara.store') }}" method="post">
    @csrf

    <!-- Informasi Perwalian -->
    <div class="text-start mt-4">
      <div class="info-container">
      <div class="info-row">
        <strong class="info-label-large">Kelas</strong><span>:</span>
        <input type="text" name="kelas" value="{{ old('kelas') }}" class="editable-input" required>
      </div>
      <div class="info-row">
        <strong class="info-label-large">Angkatan</strong><span>:</span>
        <input type="number" name="angkatan" value="{{ old('angkatan') }}" class="editable-input" required>
      </div>
      <div class="info-row">
        <strong class="info-label-large">Dosen Wali</strong><span>:</span>
        <strong>{{ session('user')['nama'] ?? 'Nama Tidak Ditemukan' }}</strong>
      </div>
      </div>
    </div>

    <!-- Box Berita Acara -->
    <div class="berita-acara-box">
      <div class="info-container">
      <div class="info-row">
        <span class="info-label">Tanggal</span><span>:</span>
        <input type="date" name="tanggal_perwalian" value="{{ old('tanggal_perwalian') }}" class="editable-input"
        required>
      </div>
      <div class="info-row">
        <span class="info-label">Perihal</span><span>:</span>
        <input type="text" name="perihal" value="{{ old('perihal') }}" class="editable-input" required>
      </div>
      <div class="info-row">
        <span class="info-label">Agenda</span><span>:</span>
        <textarea name="agenda" class="editable-textarea" rows="4" required>{{ old('agenda') }}</textarea>
      </div>
      </div>
    </div>

    <!-- Footer Informasi Halaman -->
    <div class="footer-info">
      <span class="left">IT Del/Berita Acara Perwalian</span>
      <span class="right">Halaman 1 dari 2</span>
    </div>

    <div class="page-break"></div>

    <!-- Halaman 2 -->
    <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4"
      style="max-width: 150px;">

    <h3 class="fw-bold title-centered">BERITA ACARA PERWALIAN</h3>
    <h5 class="sub-title">( Feedback dari mahasiswa selama perwalian )</h5>

    <!-- Box Berita Acara Kedua -->
    <div class="berita-acara-box">
      <div class="info-container">
      <div class="info-row">
        <span class="info-label">Hari/Tanggal</span><span>:</span>
        <input type="date" name="hari_tanggal" value="{{ old('hari_tanggal') }}" class="editable-input" required>
      </div>
      <div class="info-row">
        <span class="info-label">Perihal Perwalian</span><span>:</span>
        <input type="text" name="perihal_perwalian" value="{{ old('perihal_perwalian') }}" class="editable-input"
        required>
      </div>

      <div class="info-row">
        <span class="info-label">Catatan</span><span>:</span>
        <textarea name="catatan" class="editable-textarea" rows="4" required>{{ old('catatan') }}</textarea>
      </div>
      </div>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature-box">
      <p>
      Sitoluama, <input type="date" name="tanggal_ttd" value="{{ old('tanggal_ttd') }}" class="editable-input"
        required>
      </p>
      <br><br><br>
      <p><input type="text" name="dosen_wali_ttd" value="({{ session('user')['nama'] ?? 'Nama Tidak Ditemukan' }})"
        class="editable-input" required></p>
    </div>

    <!-- Footer Halaman 2 -->
    <div class="footer-info">
      <span class="left">IT Del/Berita Acara Perwalian</span>
      <span class="right">Halaman 2 dari 2</span>
    </div>

    <!-- Tombol Submit -->
    <div class="submit-container">
      <button type="submit" class="btn btn-success">Submit</button>
    </div>
    </form>
    <!-- Modal -->
    @if(session('kelasTerbaru') && session('tanggalPerwalian'))
    <div class="modal fade show" id="beritaAcaraModal" tabindex="-1" aria-labelledby="modalLabel" style="display: block;"
    aria-modal="true" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
      <div class="modal-header bg-success-subtle">
      <h5 class="modal-title fw-bold" id="modalLabel">Berita Acara {{ session('kelasTerbaru') }}</h5>
      </div>
      <div class="modal-body">
      <p>Terimakasih telah mengisi berita acara kelas {{ session('kelasTerbaru') }} pada perwalian</p>
      <p>{{ \Carbon\Carbon::parse(session('tanggalPerwalian'))->translatedFormat('l, d F Y') }}</p>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-success" data-bs-dismiss="modal">OKE</button>
      </div>
      </div>
    </div>
    </div>

    <script>
    // Tampilkan modal saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function () {
      var modal = new bootstrap.Modal(document.getElementById('beritaAcaraModal'));
      modal.show();
    });
    </script>
  @endif




    <style>
    .editable {
      display: inline-block;
      min-width: 50px;
      max-width: 100%;
      cursor: text;
      padding: 2px 5px;
      white-space: pre-wrap;
      word-break: break-word;
      overflow-wrap: break-word;
      text-align: left;
    }

    .editable:focus {
      outline: none;
      border-bottom: 1px solid #007bff;
    }

    .berita-acara-box {
      min-height: 700px;
      border: 2px solid #333;
      border-radius: 5px;
      padding: 15px;
      background-color: #f9f9f9;
    }

    .info-container {
      display: grid;
      grid-template-columns: max-content 10px auto;
      gap: 5px;
    }

    .info-row {
      display: contents;
    }

    .info-label-large {
      font-size: 1.2rem;
      font-weight: bold;
      text-align: left;
      white-space: nowrap;
    }

    .info-label {
      font-size: 1rem;
      font-weight: normal;
      text-align: left;
      white-space: nowrap;
    }

    .info-row span {
      align-self: start;
    }

    .title-centered {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100px;
    }

    .footer-info {
      display: flex;
      justify-content: space-between;
      margin-top: 10px;
      font-size: 0.9rem;
      font-weight: bold;
    }

    .footer-info .left {
      text-align: left;
    }

    .footer-info .right {
      text-align: right;
    }

    .page-break {
      page-break-before: always;
      margin-top: 100px;
    }

    .signature-box {
      margin-top: 40px;
      text-align: left;
    }

    .signature-box p {
      margin: 5px 0;
      font-size: 1rem;
      font-weight: bold;
    }

    .submit-container {
      display: flex;
      justify-content: flex-end;
      padding-right: 50px;
      margin-top: 50px;
    }

    .editable-input,
    .editable-textarea {
      border: none;
      background: transparent;
      outline: none;
      width: 100%;
      font-size: inherit;
      font-family: inherit;
    }

    .editable-textarea {
      min-height: 50px;
      resize: none;
    }
    </style>



    <script>
    document.addEventListener("DOMContentLoaded", function () {
      var modal = new bootstrap.Modal(document.getElementById('beritaAcaraModal'));
      modal.show();
    });
    </script>
    <!-- Titik Tanda Tangan -->
    <script>
    document.addEventListener("DOMContentLoaded", function () {
      const editableElements = document.querySelectorAll(".editable");

      editableElements.forEach((element) => {
      element.addEventListener("focus", function () {
        if (this.innerText.trim() === "..................." || this.innerText.trim() === ".......................................") {
        this.innerText = "";
        }
      });

      element.addEventListener("blur", function () {
        if (this.innerText.trim() === "") {
        this.innerText = this.getAttribute("data-placeholder") || "...................";
        }
      });

      if (!element.hasAttribute("data-placeholder")) {
        element.setAttribute("data-placeholder", element.innerText.trim());
      }
      });
    });
    </script>


  @endsection