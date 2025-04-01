@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 50vh; position: relative;">
    <!-- Tombol Back -->
    <a href="{{ url()->previous() }}" class="btn btn-primary position-absolute" style="top: 20px; left: 20px;">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <!-- Card Utama -->
    <div class="card shadow-sm border-0" style="max-width: 700px; width: 100%; min-height: 250px;">
        <div class="card-header bg-success-subtle fw-bold">
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
@endsection
