@extends('layouts.app')

@section('content')
<!-- Main Content -->
<div class="app-content-header">
    <div class="container-fluid">
        <!-- Current Date and Time -->
        <div class="d-flex justify-content-start align-items-center">
            <p class="text-muted mb-3">{{ now()->addHours(7)->isoFormat('dddd, D MMMM YYYY HH:mm') }}</p>
        </div>

        <!-- Study Progress and Attendance Frequency -->
        <div class="row">
            <!-- Study Progress Chart (Left) -->
            <div class="col-md-6">
                <div class="card p-3 shadow-sm">
                    <h5 class="card-title">Kemajuan Studi</h5>
                    <canvas id="kemajuanStudiChart" height="400"></canvas>
                </div>
            </div>

            <!-- Attendance Frequency Chart (Right) -->
            <div class="col-md-6">
                <div class="card p-3">
                    <h5 class="border-bottom-line text-start">FREKUENSI KEHADIRAN PERWALIAN MAHASISWA</h5>
                    @if (!empty($attendanceData['dates']))
                        <canvas id="frekuensiKehadiranChart" style="max-height: 400px;"></canvas>
                    @else
                        <p class="text-muted text-start">Belum ada data kehadiran.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Calendar Download Buttons -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                @if ($akademik)
                    <a href="{{ asset('storage/' . $akademik->file_path) }}" target="_blank" class="btn btn-primary me-2">
                        Unduh Kalender Akademik!
                    </a>
                @else
                    <button class="btn btn-secondary me-2" disabled>Kalender Akademik Belum Tersedia</button>
                @endif

                @if ($bem)
                    <a href="{{ asset('storage/' . $bem->file_path) }}" target="_blank" class="btn btn-primary">
                        Unduh Kalender NonAkademik!
                    </a>
                @else
                    <button class="btn btn-secondary" disabled>Kalender BEM Belum Tersedia</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>

    @if (!empty($attendanceData['dates']))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Debug: Verify Chart.js is loaded
                console.log('Chart.js loaded:', typeof Chart !== 'undefined' ? 'Yes' : 'No');

                const attendanceData = @json($attendanceData);
                console.log('Attendance Data:', attendanceData); // Debug data

                const ctx = document.getElementById('frekuensiKehadiranChart').getContext('2d');
                const values = attendanceData.values;
                const colors = attendanceData.colors;
                const dates = attendanceData.dates;

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [
                            {
                                label: 'Kehadiran',
                                data: values,
                                borderColor: (ctx) => {
                                    const index = ctx.dataIndex;
                                    if (index === undefined) {
                                        // For segments, use the color array to determine the color between points
                                        const p0 = ctx.p0DataIndex;
                                        const p1 = ctx.p1DataIndex;
                                        return values[p0] === 1 ? '#007bff' : '#dc3545';
                                    }
                                    return colors[index];
                                },
                                pointBackgroundColor: colors,
                                pointBorderColor: colors,
                                segment: {
                                    borderColor: (ctx) => {
                                        const p0 = ctx.p0DataIndex;
                                        const p1 = ctx.p1DataIndex;
                                        return values[p0] === 1 ? '#007bff' : '#dc3545';
                                    },
                                },
                                fill: false,
                                tension: 0.1,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                title: {
                                    display: false,
                                },
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: 10,
                                },
                            },
                            y: {
                                min: 0,
                                max: 1,
                                ticks: {
                                    stepSize: 1,
                                    callback: (value) => {
                                        return value === 1 ? 'Hadir' : 'Tidak Hadir';
                                    },
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'line',
                                    padding: 20,
                                    generateLabels: (chart) => {
                                        return [
                                            {
                                                text: 'Hadir',
                                                fillStyle: '#007bff',
                                                strokeStyle: '#007bff',
                                                lineWidth: 2,
                                                pointStyle: 'line',
                                            },
                                            {
                                                text: 'Tidak Hadir',
                                                fillStyle: '#dc3545',
                                                strokeStyle: '#dc3545',
                                                lineWidth: 2,
                                                pointStyle: 'line',
                                            },
                                        ];
                                    },
                                },
                            },
                            tooltip: {
                                enabled: true,
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: (context) => {
                                        const value = context.raw;
                                        return value === 1 ? 'Hadir' : 'Tidak Hadir';
                                    },
                                },
                            },
                        },
                        elements: {
                            line: {
                                borderWidth: 2,
                            },
                            point: {
                                radius: 5,
                            },
                        },
                    },
                });
            });
        </script>
    @endif
@endsection