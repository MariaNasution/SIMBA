@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('dosen') }}">Home</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>
    <div class="app-content-header">
        <div class="container-fluid">
            <!-- Announcement Banner -->
            @if(isset($perwalianAnnouncement) && !empty($perwalianAnnouncement))
                <div class="announcement-banner">
                    <div class="announcement-header">
                        <i class="far fa-bullhorn announcement-icon" aria-hidden="true"></i>
                        <h4>Pengumuman</h4>
                    </div>
                    <div class="announcement-text">
                        <p>{!! nl2br(e($perwalianAnnouncement)) !!}</p>
                    </div>
                </div>
            @endif

            <div class="row">
                @foreach ([2017, 2018, 2019, 2020] as $year)
                    @if (!empty($studentsByYear[$year]))
                        @foreach ($studentsByYear[$year] as $kelas => $students)
                            <div class="col-md-12 mb-4">
                                <div class="card p-3 shadow-sm">
                                    <h5 class="card-title">Informatika {{ $year }} - {{ $kelas }}</h5>
                                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
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
                                        <a href="{{ route('dosen.detailedClass', ['year' => $year, 'kelas' => $kelas]) }}" class="btn btn-secondary">Selengkapnya</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap');

        .announcement-banner * {
            font-family: 'Nunito Sans', sans-serif !important;
        }

        .announcement-banner {
            background-color: #f28c82;
            padding: 25px 15px;
            border-radius: 10px;
            display: block;
            margin-bottom: 20px;
            color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            width: 450px;
            min-height: 80px;
        }

        .announcement-header {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .announcement-icon {
            font-size: 24px;
            margin-right: 10px;
            color: #fff;
        }

        .announcement-header h4 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            display: inline-block;
            vertical-align: middle;
            text-align: left;
        }

        .announcement-text {
            display: block;
            width: 100%;
        }

        .announcement-text p {
            font-size: 14px;
            margin-left: 10px;
            margin-top: 10px;
            line-height: 1.5;
            text-align: left;
            font-weight: 700;
            color: #fff;
        }
    </style>
@endsection