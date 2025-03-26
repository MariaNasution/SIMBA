@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto" style="font-size: 24px; font-weight: 700; color: #333;">Pilih Kelas untuk Berita Acara</h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" style="color: #333; font-size: 24px;" title="Logout"></i>
        </a>
    </div>

    <!-- Feedback Messages -->
    <div id="feedbackMessage" class="alert alert-dismissible fade show d-none" role="alert">
        <span id="feedbackText"></span>
        <button type="button" class="btn-close" onclick="hideFeedback()"></button>
    </div>

    <!-- Class and Date Selection -->
    <div class="mb-4">
        <form id="classSelectForm" onsubmit="updateSelection(event)">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="classSelect" style="font-size: 16px; font-weight: 500; color: #333;">Pilih Kelas:</label>
                    <select id="classSelect" name="kelas" class="form-select" onchange="this.form.submit()">
                        <option value="" disabled {{ !$selectedClass ? 'selected' : '' }}>Pilih kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class }}" {{ $selectedClass === $class ? 'selected' : '' }}>{{ $class }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="dateSelect" style="font-size: 16px; font-weight: 500; color: #333;">Pilih Tanggal Perwalian:</label>
                    <select id="dateSelect" name="tanggal_perwalian" class="form-select" onchange="this.form.submit()" {{ !$selectedClass ? 'disabled' : '' }}>
                        <option value="" disabled {{ !$selectedDate ? 'selected' : '' }}>Pilih tanggal</option>
                        @foreach ($availableDates as $date)
                            <option value="{{ $date }}" {{ $selectedDate === $date ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    @if($selectedClass && $selectedDate)
        <div class="mb-4">
            <h4 style="font-size: 18px; font-weight: 600; color: #333;">Absensi Kelas {{ $selectedClass }} pada {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</h4>
            @if($absensiRecords->isNotEmpty())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Status Kehadiran</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absensiRecords as $index => $record)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $record->nim }}</td>
                                <td>{{ $record->mahasiswa->nama ?? 'Nama Tidak Ditemukan' }}</td>
                                <td>{{ ucfirst($record->status_kehadiran) }}</td>
                                <td>{{ $record->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Tidak ada data absensi untuk kelas ini pada tanggal tersebut.</p>
            @endif
        </div>

        <!-- Proceed Button -->
        <div class="text-end">
            <a href="{{ route('berita_acara.index', ['kelas' => $selectedClass, 'tanggal_perwalian' => $selectedDate]) }}"
               class="btn btn-primary">Lanjutkan ke Form Berita Acara</a>
        </div>
    @endif

    <style>
        .border-bottom-line {
            border-bottom: 1px solid #E5E5E5;
            padding-bottom: 10px;
        }

        #feedbackMessage.alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        #feedbackMessage.alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>

    <script>
        function showFeedback(message, type = 'success') {
            const feedbackMessage = document.getElementById('feedbackMessage');
            const feedbackText = document.getElementById('feedbackText');
            feedbackText.textContent = message;
            feedbackMessage.classList.remove('d-none', 'alert-success', 'alert-danger');
            feedbackMessage.classList.add(`alert-${type}`);
            setTimeout(hideFeedback, 5000);
        }

        function hideFeedback() {
            const feedbackMessage = document.getElementById('feedbackMessage');
            feedbackMessage.classList.add('d-none');
        }

        function updateSelection(event) {
            event.preventDefault();
            const form = document.getElementById('classSelectForm');
            const kelas = document.getElementById('classSelect').value;
            const tanggal = document.getElementById('dateSelect').value;

            if (kelas) {
                showFeedback(`Kelas dipilih: ${kelas}`, 'success');
            }

            if (tanggal) {
                showFeedback(`Tanggal dipilih: ${new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}`, 'success');
            }

            const url = new URL(window.location.href);
            url.searchParams.set('kelas', kelas);
            if (tanggal) {
                url.searchParams.set('tanggal_perwalian', tanggal);
            } else {
                url.searchParams.delete('tanggal_perwalian');
            }
            window.location.href = url.toString();
        }

        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = "{{ route('logout') }}";
            }
        }
    </script>
</div>
@endsection