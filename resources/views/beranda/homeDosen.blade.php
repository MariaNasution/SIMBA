@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
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
                @foreach ($studentsByYear[$year] as $kelas => $classData)
                    <div class="col-md-12 mb-4">
                        <div class="card p-3 shadow-sm">
                            <h5 class="card-title">{{ $prodisByYear[$year][$kelas] }} {{ $year }} - {{ $kelas }}</h5>
                            <div class="content-container" style="max-height: 300px; overflow-y: auto;">
                                <!-- Table -->
                                <div class="table-wrapper">
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
                                <div class="chart-wrapper" style="display: none;">
                                    <canvas class="progress-chart"></canvas>
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

        .content-container {
            position: relative;
            transition: all 0.5s ease;
        }

        .table-wrapper, .chart-wrapper {
            width: 100%;
            transition: transform 0.5s ease;
        }

        .table-wrapper {
            transform: translateX(0);
        }

        .chart-wrapper {
            transform: translateX(100%);
            position: absolute;
            top: 0;
            left: 0;
        }

        .table-wrapper.slide-out {
            transform: translateX(-100%);
        }

        .chart-wrapper.slide-in {
            transform: translateX(0);
        }
    </style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const prodisByYear = @json($prodisByYear);
    const studentsByYear = @json($studentsByYear);

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
                        const students = studentsByYear[year][kelas];
                        const semesterAverages = {};
                        students.forEach(student => {
                            const semester = student.semester || 1;
                            if (!semesterAverages[semester]) {
                                semesterAverages[semester] = { total: 0, count: 0 };
                            }
                            const ipk = parseFloat(student.ipk) || 0;
                            semesterAverages[semester].total += ipk;
                            semesterAverages[semester].count += 1;
                        });

                        const labels = [];
                        const data = [];
                        for (let sem = 1; sem <= 5; sem++) {
                            labels.push(`SEM ${sem}`);
                            if (semesterAverages[sem]) {
                                const avg = semesterAverages[sem].total / semesterAverages[sem].count;
                                data.push(avg.toFixed(2));
                            } else {
                                data.push(0);
                            }
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
                                plugins: {
                                    title: {
                                        display: true,
                                        text: `Kemajuan Studi Kelas ${prodisByYear[year][Object.keys(studentsByYear[year]).indexOf(kelas)]} ${year} - ${kelas}`,
                                        font: { size: 16 }
                                    },
                                    legend: { position: 'bottom' }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        min: 2.8,
                                        max: 3.4,
                                        ticks: { stepSize: 0.12 },
                                        title: { display: true, text: 'NR Rata-rata Kelas' }
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