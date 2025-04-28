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
                    <div class="card p-3">
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
            <div class="row gap-4 mt-4">
                <div class="col-md-12 text-center">
                    @if ($akademik)
                        <a href="{{ asset('storage/' . $akademik->file_path) }}" target="_blank" class="btn btn-primary me-4">
                            Unduh Kalender Akademik!
                        </a>
                    @else
                        <button class="btn btn-primary me-2" disabled>Kalender Akademik Belum Tersedia</button>
                    @endif

                    @if ($bem)
                        <a href="{{ asset('storage/' . $bem->file_path) }}" target="_blank" class="btn btn-primary">
                            Unduh Kalender NonAkademik!
                        </a>
                    @else
                        <button class="btn btn-primary" disabled>Kalender BEM Belum Tersedia</button>
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
                try {
                    const ctx = document.getElementById('frekuensiKehadiranChart').getContext('2d');
                    if (!ctx) {
                        throw new Error('Canvas element "frekuensiKehadiranChart" not found');
                    }

                    const attendanceData = @json($attendanceData);
                    console.log('Raw Attendance Data:', attendanceData);

                    // Validate attendance data
                    if (!attendanceData || !attendanceData.dates || !attendanceData.values || !attendanceData.colors) {
                        throw new Error('Invalid attendance data: missing required fields');
                    }

                    const dates = attendanceData.dates;
                    const values = attendanceData.values;
                    const colors = attendanceData.colors;

                    console.log('Parsed Values:', values);
                    console.log('Colors:', colors);
                    console.log('Dates:', dates);

                    // Prepare data points
                    const dataPoints = dates.map((date, index) => ({
                        x: date,
                        y: values[index]
                    }));

                    // Create the chart with a single dataset and segment-based styling
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: dates,
                            datasets: [{
                                label: 'Attendance',
                                data: dataPoints,
                                borderColor: '#007bff', // Default, overridden by segment
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

                                        console.log('Segment:', {
                                            prevIndex: prevIndex,
                                            currIndex: currIndex,
                                            prevValue: prevValue,
                                            currValue: currValue,
                                            prevDate: dates[prevIndex],
                                            currDate: dates[currIndex]
                                        });

                                        // Same status: "Hadir" to "Hadir"
                                        if (prevValue === 0.5 && currValue === 0.5) {
                                            console.log('Hadir to Hadir -> Blue');
                                            return '#007bff';
                                        }
                                        // Same status: "Tidak Hadir" to "Tidak Hadir"
                                        if (prevValue === -0.5 && currValue === -0.5) {
                                            console.log('Tidak Hadir to Tidak Hadir -> Red');
                                            return '#dc3545';
                                        }
                                        // Transition: "Hadir" to "Tidak Hadir" (downward)
                                        if (prevValue === 0.5 && currValue === -0.5) {
                                            console.log('Hadir to Tidak Hadir -> Red');
                                            return '#dc3545';
                                        }
                                        // Transition: "Tidak Hadir" to "Hadir" (upward)
                                        if (prevValue === -0.5 && currValue === 0.5) {
                                            console.log('Tidak Hadir to Hadir -> Blue');
                                            return '#007bff';
                                        }
                                        // Fallback
                                        console.log('Fallback -> Blue');
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
                                        callback: function (value) {
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
                                        generateLabels: function (chart) {
                                            return [
                                                {
                                                    text: 'Hadir',
                                                    fillStyle: '#007bff',
                                                    strokeStyle: '#007bff',
                                                    pointStyle: 'line', // Use line style for legend
                                                    lineWidth: 2
                                                },
                                                {
                                                    text: 'Tidak Hadir',
                                                    fillStyle: '#dc3545',
                                                    strokeStyle: '#dc3545',
                                                    pointStyle: 'line', // Use line style for legend
                                                    lineWidth: 2
                                                }
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