@extends('layouts.app')

@section('content')
<div class="container position-relative" style="min-height: 50vh; padding: 20px; ">
    <!-- Tombol Back -->
    <button onclick="goBack()" class="btn back-btn" aria-label="Go back">
        <span class="arrow"><</span> Back
    </button>

    <!-- Card Utama -->
    <div class="main-card">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px; width: 80%; min-height: 250px; margin-top: 60px;">
            <div class="card-header bg-success-subtle fw-bold text-center">
                Berita Acara {{ $kelas }}
            </div>
            <div class="card-body py-5 text-center">
                <p class="fs-5">Terima kasih telah mengisi berita acara kelas <strong>{{ $kelas }}</strong> pada perwalian</p>
                <p class="fs-6">{{ \Carbon\Carbon::parse($tanggal_perwalian)->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="card-footer text-end">
                <a href="{{ route('dosen') }}" class="btn btn-success px-4">OKE</a>
            </div>
        </div>
    </div>
</div>

<style>

    .main-card {
        margin-left: -60px;
    }
    

    /* Back button styling (unchanged) */
    .back-btn {
        position:inline-flex;
        top: 20px;
        left: 20px;
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        background-color: #68B8EA;
        color: #fff;
        font-size: 18px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .back-btn:hover {
        background-color: #4A9CD6;
    }

    .back-btn .arrow {
        font-size: 40px;
        margin-right: 0px;
        line-height: 1;
    }

    /* Ensure card header content is centered */
    .card-header {
        padding: 10px 15px;
        background-color: #e6f4ea; /* Light green from the image */
    }

    /* Style the OKE button to match the image */
    .btn-success {
        background-color: #28a745; /* Green color from the image */
        border-color: #28a745;
    }

    .btn-success:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }

    @media (max-width: 576px) {
        .back-btn {
            top: 10px;
            left: 10px;
            font-size: 16px;
            padding: 2px 6px;
        }

        .back-btn .arrow {
            font-size: 30px;
            margin-right: 0px;
        }

        .card {
            margin-top: 50px;
            margin-left: 20px; /* Reduced margin on smaller screens */
        }
    }
</style>

<script>
    function goBack() {
        window.history.back();
    }
</script>
@endsection