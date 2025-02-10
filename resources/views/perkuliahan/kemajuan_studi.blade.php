@extends('layouts.app')


@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('beranda') }}">Home</a> /
            <a href="{{ route('kemajuan_studi') }}">Perkuliahan</a> /
            <a href="{{ route('kemajuan_studi') }}">Kemajuan Studi</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div class="container mt-4">
        <!-- Grafik Kemajuan Studi -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card p-3 shadow-sm">
                    <h5 class="card-title">Grafik Kemajuan Studi</h5>
                    <canvas id="kemajuanStudiChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- IP, NR, IPS -->

        <div class="col mt-4">
            <div class="row">
                <div class="col-4">
                    <h5 class="card-title">IP : {{ $data['IP'] }}</h5>
                </div>
            </div>
        </div>


        <!-- Tabel Mata Kuliah per Semester -->
        @foreach ($matkulPerSemester as $semester => $matkuls)
            <div class="row justify-content-center mt-4">

                <h5 class="card-title">Mata Kuliah {{ $semester }}</h5>

                <!-- Menampilkan IP Semester di atas tabel -->
                @foreach ($sortedSemesterData as $semesterData)
                    @if ($semesterData['semester'] == $semester)
                        <div class="col-md-12 text-end">
                            <p><strong>
                                    <span class="mark-yellow">
                                        NR/IPS: {{ $semesterData['ip_semester'] }}
                                    </span>
                                </strong></p>
                        </div>
                    @endif
                @endforeach

                <!-- Tabel Mata Kuliah -->
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Kode Mata Kuliah</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Nilai Akhir</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($matkuls as $matkul)
                            <tr>
                                <td>{{ $matkul['kode_mk'] }}</td>
                                <td><a
                                        href="{{ route('detailnilai', ['kode_mk' => $matkul['kode_mk']]) }}">{{ $matkul['nama_kul_ind'] }}</a>
                                </td>
                                <td>{{ $matkul['sks'] }}</td>
                                <td>{{ $matkul['na'] }}</td>
                                <td>{{ $matkul['nilai'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data untuk grafik
        const labels = {!! json_encode($labels) !!};
        const dataValues = {!! json_encode($values) !!};

        const ctx = document.getElementById('kemajuanStudiChart').getContext('2d');
        const kemajuanStudiChart = new Chart(ctx, {
            type: 'line', // Menggunakan line chart
            data: {
                labels: labels, // Label sumbu X
                datasets: [{
                    label: 'IP Semester',
                    data: dataValues, // Nilai IP untuk grafik
                    fill: false,
                    borderColor: '#007bff', // Warna garis grafik
                    tension: 0.1 // Kurva garis
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Indeks Prestasi (IP)'
                        }
                    }
                }
            }
        });
    </script>
@endsection
