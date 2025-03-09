@extends('layouts.app')
@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('dosen') }}">Home</a> / <span>Detailed Class</span>
        </h3>
        <a href="{{ route('dosen') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-start align-items-center">
                <p class="text-muted mb-3">{{ now()->addHours(7)->isoFormat('dddd, D MMMM YYYY HH:mm') }}</p>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card p-3 shadow-sm">
                        <h5 class="card-title">Informatika 2022 {{ $class }}</h5>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>NIM</th>
                                        <th>Nama</th>
                                        <th>Semester</th>
                                        <th>IPK</th>
                                        <th>IPS</th>
                                        <th>Status KRS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $student)
                                        <tr>
                                            <td>{{ $student['nim'] ?? 'N/A' }}</td>
                                            <td>{{ $student['nama'] ?? 'N/A' }}</td>
                                            <td>{{ $student['semester'] ?? 'N/A' }}</td>
                                            <td>{{ $student['ipk'] ?? 'N/A' }}</td>
                                            <td>{{ $student['ips'] ?? 'N/A' }}</td>
                                            <td>{{ $student['status_krs'] ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-2">
                            <!-- Optional: a button to proceed to next page of details -->
                            <a href="#" class="btn btn-secondary">Selanjutnya</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
