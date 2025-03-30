@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $beritaAcara->judul }}</h1>
        <p><strong>Tanggal:</strong> {{ $beritaAcara->tanggal }}</p>
        <p><strong>Deskripsi:</strong></p>
        <p>{{ $beritaAcara->deskripsi }}</p>
        <a href="{{ route('berita_acara.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
@endsection