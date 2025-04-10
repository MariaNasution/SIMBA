@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Perwalian / Berita Acara</h1>
    
    <div class="row mt-4">
        <!-- Dropdown Pilih Prodi -->
        <div class="col-md-3">
            <label for="pilihProdi" class="form-label">Pilih Prodi</label>
            <select class="form-select custom-select" id="pilihProdi" name="pilihProdi">
                <option selected disabled>Pilih Prodi</option>
                <option>S1 Informatika</option>
                <option>S1 Sistem Informasi</option>
                <option>S1 Teknik Elektro</option>
                <option>D3 Teknologi Informasi</option>
                <option>D3 Teknologi Komputer</option>
                <option>D4 Teknologi Rekayasa Perangkat Lunak</option>
                <option>S1 Manajemen Rekayasa</option>
                <option>S1 Teknik Metalurgi</option>
                <option>S1 Bioproses</option>
            </select>
        </div>

        <!-- Dropdown Keterangan -->
        <div class="col-md-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <select class="form-select custom-select" id="keterangan" name="keterangan">
                <option selected disabled>Keterangan</option>
                <option>Semester Baru</option>
                <option>Sebelum UTS</option>
                <option>Sebelum UAS</option>
            </select>
        </div>

        <!-- Dropdown Angkatan -->
        <div class="col-md-3">
            <label for="angkatan" class="form-label">Angkatan</label>
            <select class="form-select custom-select" id="angkatan" name="angkatan">
                <option selected disabled>Angkatan</option>
                <option>2014</option>
                <option>2015</option>
                <option>2016</option>
                <option>2017</option>
                <option>2018</option>
                <option>2019</option>
                <option>2020</option>
                <option>2021</option>
                <option>2022</option>
                <option>2023</option>
                <option>2024</option>
                <option>2025</option>
            </select>
        </div>

        <!-- Tombol Terapkan sejajar dengan dropdown -->
        <div class="col-md-3">
            <div class="w-100">
                <label class="form-label invisible">Terapkan</label> <!-- untuk mempertahankan tinggi -->
                <button class="btn btn-primary w-100 custom-btn">Terapkan</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Custom styling untuk dropdown */
    .custom-select {
        background-color: #F5F5F5; /* Warna abu-abu muda untuk dropdown utama */
        border: 1px solid #E0E0E0;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
        color: #757575;
    }

    /* Warna background untuk opsi dropdown */
    .custom-select option {
        background-color: #E8F5E9; /* Warna hijau muda untuk opsi */
        color: #424242;
    }

    /* Custom styling untuk tombol */
    .custom-btn {
        background-color: #2E7D32; /* Warna hijau tua untuk tombol */
        border: none;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
    }

    .custom-btn:hover {
        background-color: #1B5E20; /* Warna hijau lebih gelap saat hover */
    }

    /* Menyesuaikan label */
    .form-label {
        font-size: 14px;
        color: #424242;
        margin-bottom: 5px;
    }
</style>
@endsection