@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Riwayat Berita Acara</h1>
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <a href="{{ route('berita_acara.create') }}" class="btn btn-primary mb-3">Tambah Berita Acara</a>
        <table class="table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($beritaAcaras as $berita)
                    <tr>
                        <td>{{ $berita->judul }}</td>
                        <td>{{ $berita->tanggal }}</td>
                        <td>
                            <a href="{{ route('berita_acara.show', $berita->id) }}" class="btn btn-info btn-sm">Lihat</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection