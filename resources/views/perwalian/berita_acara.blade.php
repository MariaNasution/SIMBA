@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto" style="font-size: 24px; font-weight: 700; color: #333;">Buat Berita Acara</h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" style="color: #333; font-size: 24px;" title="Logout"></i>
        </a>
    </div>

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('dosen') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <!-- Feedback Messages -->
    <div id="feedbackMessage" class="alert alert-dismissible fade show d-none" role="alert">
        <span id="feedbackText"></span>
        <button type="button" class="btn-close" onclick="hideFeedback()"></button>
    </div>

    <!-- Class Selection (Buttons) -->
    <div class="mb-4">
        <h4 style="font-size: 18px; font-weight: 600; color: #333;">Pilih Kelas:</h4>
        <div class="d-flex flex-wrap gap-2">
            @if (empty($classes))
                <p class="text-muted">Tidak ada kelas yang ditugaskan untuk Anda.</p>
            @else
                @foreach ($classes as $class)
                    <a href="{{ route('berita_acara.select_class', ['kelas' => $class]) }}"
                       class="btn {{ $selectedClass === $class ? 'btn-primary' : 'btn-outline-primary' }}">
                        Kelas {{ $class }}
                    </a>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Perwalian Selection (Dropdown) -->
    @if ($selectedClass)
        <div class="mb-4">
            <form id="perwalianSelectForm" onsubmit="updateSelection(event)">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="perwalianSelect" style="font-size: 16px; font-weight: 500; color: #333;">Pilih Perwalian:</label>
                        <select id="perwalianSelect" name="tanggal_perwalian" class="form-select" onchange="this.form.submit()">
                            <option value="" disabled {{ !$selectedDate ? 'selected' : '' }}>Pilih perwalian</option>
                            @if ($availablePerwalians->isEmpty())
                                <option value="" disabled>Tidak ada perwalian selesai untuk kelas ini</option>
                            @else
                                @foreach ($availablePerwalians as $perwalianItem)
                                    <option value="{{ $perwalianItem->Tanggal }}" {{ $selectedDate === $perwalianItem->Tanggal ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($perwalianItem->Tanggal)->translatedFormat('l, d F Y') }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </form>
        </div>
    @endif

    <!-- Absensi Table and Berita Acara Form -->
    @if($selectedClass && $selectedDate && $perwalian)
        <!-- Absensi Table -->
        <div class="mb-4">
            <h4 style="font-size: 18px; font-weight: 600; color: #333;">Absensi Kelas {{ $selectedClass }} pada {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}</h4>
            @if($absensiRecords->isNotEmpty())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Mahasiswa</th>
                            <th>Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($absensiRecords as $record)
                            <tr>
                                <td>{{ $record->mahasiswa->nama ?? 'Nama Tidak Ditemukan' }}</td>
                                <td>
                                    @if($record->status_kehadiran === 'hadir')
                                        <span class="text-success"><i class="fas fa-check"></i> Hadir</span>
                                    @elseif($record->status_kehadiran === 'alpa')
                                        <span class="text-danger"><i class="fas fa-times"></i> Alpa</span>
                                    @elseif($record->status_kehadiran === 'izin')
                                        <span class="text-warning"><i class="fas fa-exclamation-circle"></i> Izin</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-muted">Tidak ada data absensi untuk perwalian ini.</p>
            @endif
        </div>

        <!-- Berita Acara Form -->
        <div class="text-center">
            <!-- Logo Kampus -->
            <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">

            <!-- Judul Halaman -->
            <h3 class="fw-bold title-centered">AGENDA PERWALIAN</h3>

            <form id="beritaAcaraForm" onsubmit="handleFormSubmit(event)">
                @csrf

                <!-- Informasi Perwalian -->
                <div class="text-start mt-4">
                    <div class="info-container">
                        <div class="info-row">
                            <strong class="info-label-large">Kelas</strong><span>:</span>
                            <input type="text" name="kelas" value="{{ $selectedClass }}" class="editable-input" readonly>
                        </div>
                        <div class="info-row">
                            <strong class="info-label-large">Angkatan</strong><span>:</span>
                            <input type="number" name="angkatan" value="{{ old('angkatan') }}" class="editable-input" required oninput="showFieldFeedback('Angkatan', this)">
                        </div>
                        <div class="info-row">
                            <strong class="info-label-large">Dosen Wali</strong><span>:</span>
                            <strong>{{ session('user')['username'] ?? 'Nama Tidak Ditemukan' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Box Berita Acara -->
                <div class="berita-acara-box">
                    <div class="info-container">
                        <div class="info-row">
                            <span class="info-label">Tanggal</span><span>:</span>
                            <input type="date" name="tanggal_perwalian" value="{{ $selectedDate }}" class="editable-input" readonly>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Perihal</span><span>:</span>
                            <input type="text" name="perihal_perwalian" value="{{ old('perihal_perwalian') }}" class="editable-input" required oninput="showFieldFeedback('Perihal Perwalian', this)">
                        </div>
                        <div class="info-row">
                            <span class="info-label">Agenda</span><span>:</span>
                            <textarea name="agenda" class="editable-textarea" rows="4" required oninput="showFieldFeedback('Agenda', this)">{{ old('agenda') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer Informasi Halaman -->
                <div class="footer-info">
                    <span class="left">IT Del/Berita Acara Perwalian</span>
                    <span class="right">Halaman 1 dari 2</span>
                </div>

                <div class="page-break"></div>

                <!-- Halaman 2 -->
                <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">

                <h3 class="fw-bold title-centered">BERITA ACARA PERWALIAN</h3>
                <h5 class="sub-title">( Feedback dari mahasiswa selama perwalian )</h5>

                <!-- Box Berita Acara Kedua -->
                <div class="berita-acara-box">
                    <div class="info-container">
                        <div class="info-row">
                            <span class="info-label">Hari/Tanggal</span><span>:</span>
                            <input type="date" name="hari_tanggal" value="{{ old('hari_tanggal') }}" class="editable-input"
                                   min="2025-01-01" max="2027-12-31" required oninput="showFieldFeedback('Hari/Tanggal Feedback', this)">
                        </div>
                        <div class="info-row">
                            <span class="info-label">Perihal Feedback</span><span>:</span>
                            <input type="text" name="perihal2" value="{{ old('perihal2') }}" class="editable-input" oninput="showFieldFeedback('Perihal Feedback', this)">
                        </div>
                        <div class="info-row">
                            <span class="info-label">Catatan</span><span>:</span>
                            <textarea name="catatan" class="editable-textarea" rows="4" oninput="showFieldFeedback('Catatan', this)">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Tanda Tangan -->
                <div class="signature-box">
                    <p>
                        Sitoluama, <input type="date" name="tanggal_ttd" value="{{ old('tanggal_ttd') }}" class="editable-input"
                                         min="2025-01-01" max="2027-12-31" required oninput="showFieldFeedback('Tanggal Tanda Tangan', this)">
                    </p>
                    <br><br><br>
                    <p><input type="text" name="dosen_wali_ttd" value="({{ session('user')['username'] ?? 'Nama Tidak Ditemukan' }})"
                              class="editable-input" required oninput="showFieldFeedback('Dosen Wali TTD', this)"></p>
                </div>

                <!-- Footer Halaman 2 -->
                <div class="footer-info">
                    <span class="left">IT Del/Berita Acara Perwalian</span>
                    <span class="right">Halaman 2 dari 2</span>
                </div>

                <!-- Tombol Submit -->
                <div class="submit-container">
                    <button type="submit" class="btn btn-success" id="submitButton">
                        <span id="buttonText">Submit</span>
                        <span id="buttonLoading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>

            <!-- Modal for Success -->
            <div class="modal fade" id="beritaAcaraModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success-subtle">
                            <h5 class="modal-title fw-bold" id="modalLabel">Berita Acara</h5>
                        </div>
                        <div class="modal-body">
                            <p id="modalMessage"></p>
                            <p id="modalDate"></p>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ route('dosen') }}" class="btn btn-success">OKE</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($selectedClass && $selectedDate)
        <p class="text-muted">Perwalian ini belum selesai, tidak memiliki absensi, atau sudah memiliki berita acara.</p>
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

        .editable {
            display: inline-block;
            min-width: 50px;
            max-width: 100%;
            cursor: text;
            padding: 2px 5px;
            white-space: pre-wrap;
            word-break: break-word;
            overflow-wrap: break-word;
            text-align: left;
        }

        .editable:focus {
            outline: none;
            border-bottom: 1px solid #007bff;
        }

        .berita-acara-box {
            min-height: 700px;
            border: 2px solid #333;
            border-radius: 5px;
            padding: 15px;
            background-color: #f9f9f9;
        }

        .info-container {
            display: grid;
            grid-template-columns: max-content 10px auto;
            gap: 5px;
        }

        .info-row {
            display: contents;
        }

        .info-label-large {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: left;
            white-space: nowrap;
        }

        .info-label {
            font-size: 1rem;
            font-weight: normal;
            text-align: left;
            white-space: nowrap;
        }

        .info-row span {
            align-self: start;
        }

        .title-centered {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100px;
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .footer-info .left {
            text-align: left;
        }

        .footer-info .right {
            text-align: right;
        }

        .page-break {
            page-break-before: always;
            margin-top: 100px;
        }

        .signature-box {
            margin-top: 40px;
            text-align: left;
        }

        .signature-box p {
            margin: 5px 0;
            font-size: 1rem;
            font-weight: bold;
        }

        .submit-container {
            display: flex;
            justify-content: flex-end;
            padding-right: 50px;
            margin-top: 50px;
        }

        .editable-input,
        .editable-textarea {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: inherit;
            font-family: inherit;
        }

        .editable-textarea {
            min-height: 50px;
            resize: none;
        }

        .btn-outline-primary {
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            background-color: #e9ecef;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>

    <script>
        const storeRoute = "{{ route('berita_acara.store') }}";

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

        function showFieldFeedback(fieldName, element) {
            if (element.value.trim()) {
                showFeedback(`${fieldName} diisi: ${element.value}`, 'success');
            } else {
                showFeedback(`${fieldName} dikosongkan.`, 'danger');
            }
        }

        function updateSelection(event) {
            event.preventDefault();
            const form = document.getElementById('perwalianSelectForm');
            const kelas = "{{ $selectedClass }}";
            const tanggal = document.getElementById('perwalianSelect').value;

            if (tanggal) {
                showFeedback(`Perwalian dipilih: ${new Date(tanggal).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}`, 'success');
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

        function handleFormSubmit(event) {
            event.preventDefault();
            const form = document.getElementById('beritaAcaraForm');
            const submitButton = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');
            const buttonLoading = document.getElementById('buttonLoading');

            // Show loading state
            submitButton.disabled = true;
            buttonText.classList.add('d-none');
            buttonLoading.classList.remove('d-none');

            const formData = new FormData(form);

            fetch(storeRoute, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                // Reset button state
                submitButton.disabled = false;
                buttonText.classList.remove('d-none');
                buttonLoading.classList.add('d-none');

                if (data.success) {
                    // Show success modal
                    const modal = new bootstrap.Modal(document.getElementById('beritaAcaraModal'));
                    document.getElementById('modalLabel').textContent = `Berita Acara ${data.kelas}`;
                    document.getElementById('modalMessage').textContent = `Terima kasih telah mengisi berita acara kelas ${data.kelas} pada perwalian`;
                    document.getElementById('modalDate').textContent = new Date(data.tanggal_perwalian).toLocaleDateString('id-ID', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    modal.show();

                    // Reset form
                    form.reset();
                } else {
                    showFeedback(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                submitButton.disabled = false;
                buttonText.classList.remove('d-none');
                buttonLoading.classList.add('d-none');
                showFeedback('Gagal mengirimkan form. Silakan coba lagi.', 'danger');
            });
        }

        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = "{{ route('logout') }}";
            }
        }
    </script>
</div>
@endsection