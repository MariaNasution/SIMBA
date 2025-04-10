@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center py-2">
            <h4 class="ms-3 mb-0">Perwalian / Jadwalkan Perwalian</h4>
            <a href="#" class="me-3 text-white"><i class="fas fa-arrow-right"></i></a>
        </div>

        <!-- Form Jadwal -->
        <div class="row mt-4 mx-2">
            <div class="col-md-8">
                <h5>Jadwalkan Perwalian</h5>

                <div class="row">
                    <!-- Jadwal Mulai -->
                    <div class="col-md-6 mb-3">
                        <label for="jadwalMulai" class="form-label">Jadwal Mulai</label>
                        <div class="input-group">
                            <input type="datetime-local" class="form-control custom-input" id="jadwalMulai"
                                name="jadwalMulai" value="2025-03-20T01:45">
                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                        </div>
                    </div>

                    <!-- Jadwal Selesai -->
                    <div class="col-md-6 mb-3">
                        <label for="jadwalSelesai" class="form-label">Jadwal Selesai</label>
                        <div class="input-group">
                            <input type="datetime-local" class="form-control custom-input" id="jadwalSelesai"
                                name="jadwalSelesai">
                            <span class="input-group-text"><i class="far fa-clock"></i></span>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Keterangan -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <select class="form-select custom-select" id="keterangan" name="keterangan">
                            <option selected disabled>Pilih Keterangan</option>
                            <option>Semester Baru</option>
                            <option>Sebelum UTS</option>
                            <option>Sebelum UAS</option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Jadwalkan -->
                <div class="row mt-2">
                    <div class="col-md-3">
                        <button class="btn custom-btn w-100">Jadwalkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Custom styling untuk input */
        .custom-input {
            background-color: #F5F5F5;
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            color: #757575;
        }

        /* Custom styling untuk dropdown */
        .custom-select {
            background-color: #F5F5F5;
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            color: #757575;
        }

        /* Custom styling untuk tombol */
        .custom-btn {
            background-color: #2E7D32;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
        }

        .custom-btn:hover {
            background-color: #1B5E20;
        }

        /* Menyesuaikan label */
        .form-label {
            font-size: 14px;
            color: #424242;
            margin-bottom: 6px;
            /* Jarak yang lebih lega dari label ke input */
        }
    </style>
@endsection