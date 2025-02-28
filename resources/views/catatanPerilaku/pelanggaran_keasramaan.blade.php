@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 border-bottom">
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('catatan_perilaku') }}">Catatan Perilaku</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <!-- Konten Utama -->
    <div class="container mt-4">
        <h3 class="text-center mb-4">Daftar Nilai Perilaku Mahasiswa</h3>
        <hr class="mb-4">

    
    </div>
@endsection
