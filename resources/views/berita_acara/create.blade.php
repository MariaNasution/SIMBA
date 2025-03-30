@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Tambah Berita Acara</h1>
        <form action="{{ route('berita_acara.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" name="judul" id="judul" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="tanggal">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success mt-3">Simpan</button>
            <a href="{{ route('berita_acara.index') }}" class="btn btn-secondary mt-3">Kembali</a>
        </form>
    </div>
@endsection