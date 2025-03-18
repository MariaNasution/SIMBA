@extends('layouts.app')

@section('content')
<div class="container text-center">
  <!-- Logo Kampus -->
  <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">

  <!-- Judul Halaman -->
  <h3 class="fw-bold title-centered">AGENDA PERWALIAN</h3>

  <!-- Informasi Perwalian -->
  <div class="text-start mt-4">
    <div class="info-container">
      <div class="info-row">
        <strong class="info-label-large">Kelas</strong><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
      <div class="info-row">
        <strong class="info-label-large">Angkatan</strong><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
      <div class="info-row">
        <strong class="info-label-large">Dosen Wali</strong><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
    </div>
  </div>

  <!-- Box Berita Acara -->
  <div class="berita-acara-box">
    <div class="info-container">
      <div class="info-row">
        <span class="info-label">Tanggal</span><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Perihal</span><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Agenda</span><span>:</span> <span contenteditable="true" class="editable agenda-field"></span>
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
  <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">

  <h3 class="fw-bold title-centered">BERITA ACARA PERWALIAN</h3>
  <h5 class="sub-title">( Feedback dari mahasiswa selama perwalian )</h5>

  <!-- Box Berita Acara Kedua -->
  <div class="berita-acara-box">
    <div class="info-container">
      <div class="info-row">
        <span class="info-label">Hari/Tanggal</span><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Perihal</span><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
      <div class="info-row">
        <span class="info-label">Catatan</span><span>:</span> <span contenteditable="true" class="editable"></span>
      </div>
    </div>
  </div>

  <!-- Tanda Tangan -->
  <div class="signature-box">
    <p>
      Sitoluama, <span contenteditable="true" class="editable">...................</span>
    </p>
    <p>Dosen Wali,</p>
    <br><br><br>
    <p>( <span contenteditable="true" class="editable">.......................................</span> )</p>
  </div>

  <!-- Footer Halaman 2 -->
  <div class="footer-info">
    <span class="left">IT Del/Berita Acara Perwalian</span>
    <span class="right">Halaman 2 dari 2</span>
  </div>
</div>

<!-- Tombol Submit -->
<div class="submit-container">
  <button class="btn btn-success">Submit</button>
</div>


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
</style>




<!-- Titik Tanda Tangan -->
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const editableElements = document.querySelectorAll(".editable");

    editableElements.forEach((element) => {
      element.addEventListener("focus", function() {
        if (this.innerText.trim() === "..................." || this.innerText.trim() === ".......................................") {
          this.innerText = "";
        }
      });

      element.addEventListener("blur", function() {
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