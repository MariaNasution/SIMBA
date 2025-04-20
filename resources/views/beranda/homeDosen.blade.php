@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    @if(isset($perwalianAnnouncement) && !empty($perwalianAnnouncement))
        <div class="announcement-banner">
            <div class="announcement-header">
                <!-- Replace Font Awesome icon with custom image -->
                <img src="{{ asset('assets/img/announcement_line.png') }}" alt="Announcement Icon" class="announcement-icon">
                <h5>Pengumuman</h5>
            </div>
            <div class="announcement-text">
                <p>{!! nl2br(e($perwalianAnnouncement)) !!}</p>
            </div>
        </div>
    @endif

    <div class="row">
        @foreach ([2017, 2018, 2019, 2020] as $year)
            @if (!empty($studentsByYear[$year]))
                @foreach ($studentsByYear[$year] as $kelas => $classData)
                    <div class="col-md-12 mb-4">
                        <div class="card p-3">
                            <h5 class="card-title">{{ $prodisByYear[$year][$kelas] }} {{ $angkatanByKelasAndYear[$year][$kelas] }} - {{ $kelas }}</h5>
                            <div class="content-container" style="height: 300px; position: relative;">
                                <!-- Table -->
                                <div class="table-wrapper">
                                    <table class="table table-bordered">
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
                                            @foreach ($classData as $student)
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
                                <!-- Chart -->
                                <div class="chart-wrapper" style="display: none; height: 100%;">
                                    <canvas class="progress-chart" style="max-height: 100%; max-width: 100%;"></canvas>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <a href="#" class="btn btn-primary toggle-chart" data-year="{{ $year }}" data-kelas="{{ $kelas }}">Kemajuan Studi</a>
                                <a href="{{ route('dosen.detailedClass', ['year' => $year, 'kelas' => $kelas]) }}" class="btn btn-secondary">Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        @endforeach
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

    .announcement-header h5 {
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

    .card-title {
        text-align: left;
        margin-bottom: 15px;
    }
    
    .card {
        background-color: transparent;
        box-shadow: none;
    }

    .content-container {
        transition: all 0.5s ease;
        overflow-y: auto;
    }

    .table-wrapper, .chart-wrapper {
        width: 100%;
        height: 100%;
        transition: transform 0.5s ease;
        position: absolute;
        top: 0;
        left: 0;
    }

    .table-wrapper {
        transform: translateX(0);
    }

    .chart-wrapper {
        transform: translateX(100%);
    }

    .table-wrapper.slide-out {
        transform: translateX(-100%);
    }

    .chart-wrapper.slide-in {
        transform: translateX(0);
    }

    .table {
        border-collapse: collapse;
    }

    .table th {
        padding-left: 8px;
        padding-right: 8px;
        padding-top: 4px;
        padding-bottom: 4px;
        border: 1px solid #333;
    }
   
    .table td {
        border: 1px solid #333;
        padding: 8px;
    }

    .table tbody td {
        color: #333;
        background-color: #DFF0D8;
    }

    .table tbody tr:first-child td {
        background-color: #F2DEDE;
    }

    .table thead th {
        background-color: #fff;
        color: #333;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const prodisByYear = @json($prodisByYear);
    const semesterAveragesByYear = @json($semesterAveragesByYear);
    const angkatanByKelasAndYear = @json($angkatanByKelasAndYear);

    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-chart');
        
        toggleButtons.forEach(button => {
            let chartInitialized = false;
            let chartInstance = null;
            
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const card = this.closest('.card');
                const tableWrapper = card.querySelector('.table-wrapper');
                const chartWrapper = card.querySelector('.chart-wrapper');
                const canvas = chartWrapper.querySelector('.progress-chart');
                const year = this.getAttribute('data-year');
                const kelas = this.getAttribute('data-kelas');
                const angkatan = angkatanByKelasAndYear[year][kelas] || year;
                
                if (tableWrapper.classList.contains('slide-out')) {
                    tableWrapper.classList.remove('slide-out');
                    chartWrapper.classList.remove('slide-in');
                    setTimeout(() => {
                        chartWrapper.style.display = 'none';
                    }, 500);
                    this.textContent = 'Kemajuan Studi';
                } else {
                    tableWrapper.classList.add('slide-out');
                    chartWrapper.style.display = 'block';
                    setTimeout(() => {
                        chartWrapper.classList.add('slide-in');
                    }, 10);
                    this.textContent = 'Kembali ke Tabel';
                    
                    if (!chartInitialized) {
                        const semesterAverages = semesterAveragesByYear[year][kelas] || {};
                        const maxSemester = Math.max(...Object.keys(semesterAverages).map(Number), 5);

                        const labels = [];
                        const data = [];
                        for (let sem = 1; sem <= maxSemester; sem++) {
                            labels.push(`Semester ${sem}`);
                            data.push(semesterAverages[sem] || 0);
                        }

                        chartInstance = new Chart(canvas, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'NR Rata-rata Kelas',
                                    data: data,
                                    borderColor: 'rgba(200, 0, 0, 1)',
                                    backgroundColor: 'rgba(200, 0, 0, 0.2)',
                                    fill: false,
                                    tension: 0.1,
                                    pointRadius: 5,
                                    pointBackgroundColor: 'rgba(200, 0, 0, 1)',
                                    pointBorderColor: 'rgba(200, 0, 0, 1)'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: `Progress Pencapaian ${prodisByYear[year][kelas]} ${angkatan} - ${kelas}`,
                                        font: { size: 16 },
                                        color: 'black'
                                    },
                                    legend: { position: 'bottom' }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        min: 2.0,
                                        max: 4.0,
                                        ticks: { stepSize: 0.5 },
                                        title: { display: true, text: 'Index Prestasi' }
                                    },
                                    x: { title: { display: true, text: 'Semester' } }
                                }
                            }
                        });
                        chartInitialized = true;
                    }
                }
            });
        });
    });
</script>
@endsection