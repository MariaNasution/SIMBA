@extends('layouts.app')

@section('content')
<!-- Main Content -->
<div class="app-content-header">
    <div class="container">
        <!-- Current Date and Time -->
        <div class="d-flex justify-content-start align-items-center mb-4">
            <p class="text-muted mb-0">{{ now()->addHours(7)->isoFormat('dddd, D MMMM YYYY HH:mm') }}</p>
        </div>

        <!-- Study Progress and Attendance Frequency -->
        <div class="row g-4">
            <!-- Study Progress Chart (Left) -->
            <div class="col-md-6 d-flex">
                <div class="card p-3 shadow-sm flex-fill">
                    <h5 class="card-title mb-3">Kemajuan Studi</h5>
                    <div class="chart-container">
                        <canvas id="kemajuanStudiChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Attendance Frequency Chart (Right) -->
            <div class="col-md-6 d-flex">
                <div class="card p-3 flex-fill">
                    <h5 class="card-title mb-3">Frekuensi Kehadiran Perwalian Mahasiswa</h5>
                    @if (!empty($attendanceData['dates']))
                        <div class="chart-container">
                            <canvas id="frekuensiKehadiranChart"></canvas>
                        </div>
                    @else
                        <p class="text-muted">Belum ada data kehadiran.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Calendar Download Buttons -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    @if ($akademik)
                        <污 href="{{ asset('storage/' . $akademik->file_path) }}" target="_blank" class="btn btn-primary">
                            Unduh Kalender Akademik!
                        </a>
                    @else
                        <button class="btn btn-secondary" disabled>Kalender Akademik Belum Tersedia</button>
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
</div>
@endsection

@section('styles')
<style>
.chart-container {
    position: relative;
    width: 100%;
    height: 400px; /* Consistent height for charts */
}

.card {
    display: flex;
    flex-direction: column;
}

.card .chart-container {
    flex-grow: 1;
}

@media (max-width: 767.98px) {
    .chart-container {
        height: 300px; /* Smaller height on mobile */
    }
}
</style>
@endsection

@section('scripts')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>

@if (!empty($attendanceData['dates']))
<script>
document.addEventListener('DOMContentLoaded', function () {
    try {
        const ctx = document.getElementById('frekuensiKehadiranChart').getContext('2d');
        if (!ctx) {
            throw new Error('Canvas element "frekuensiKehadiranChart" not found');
        }

        const attendanceData = @json($attendanceData);

        // Validate attendance data
        if (!attendanceData || !attendanceData.dates || !attendanceData.values || !attendanceData.colors) {
            throw new Error('Invalid attendance data: missing required fields');
        }

        const dates = attendanceData.dates;
        const values = attendanceData.values;
        const colors = attendanceData.colors;

        // Prepare data points
        const dataPoints = dates.map((date, index) => ({
            x: date,
            y: values[index]
        }));

        // Create the chart
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Attendance',
                    data: dataPoints,
                    borderColor: '#007bff',
                    backgroundColor: colors,
                    pointBackgroundColor: colors,
                    pointBorderColor: colors,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: false,
                    tension: 0,
                    segment: {
                        borderColor: ctx => {
                            const prevIndex = ctx.p0DataIndex;
                            const currIndex = ctx.p1DataIndex;
                            const prevValue = values[prevIndex];
                            const currValue = values[currIndex];

                            if (prevValue === 0.5 && currValue === 0.5) return '#007bff';
                            if (prevValue === -0.5 && currValue === -0.5) return '#dc3545';
                            if (prevValue === 0.5 && currValue === -0.5) return '#dc3545';
                            if (prevValue === -0.5 && currValue === 0.5) return '#007bff';
                            return '#007bff';
                        }
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: -1,
                        max: 1,
                        ticks: {
                            stepSize: 1,
                            callback: function(value) {
                                if (value === 0.5) return 'Hadir';
                                if (value === -0.5) return 'Tidak Hadir';
                                return '';
                            }
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            generateLabels: function(chart) {
                                return [
                                    { text: 'Hadir', fillStyle: '#007bff', strokeStyle: '#007bff', pointStyle: 'line', lineWidth: 2 },
                                    { text: 'Tidak Hadir', fillStyle: '#dc3545', strokeStyle: '#dc3545', pointStyle: 'line', lineWidth: 2 }
                                ];
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error rendering attendance chart:', error);
    }
});
</script>
@endif
@endsection