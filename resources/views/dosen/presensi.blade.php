@extends('layouts.app')
@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('dosen.presensi') }}">Presensi</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-start align-items-center">
                <p class="text-muted mb-3">{{ now()->addHours(7)->isoFormat('dddd, D MMMM YYYY HH:mm') }}</p>
            </div>
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="card p-3 shadow-sm">
                        <h5 class="card-title">Presensi Mahasiswa (Anak Wali)</h5>
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Username Mahasiswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($anakWali as $mahasiswa)
                                    <tr>
                                        <td>{{ $mahasiswa->username }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td>No anak wali found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection