@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- Back Button -->
        <div class="mb-3">
            <button onclick="goBack()" class="btn back-btn">
                <span class="arrow"><</span>Back
            </button>
        </div>

        <!-- Feedback Messages -->
        @if (session('error'))
            <div id="feedbackMessage" class="alert alert-dismissible fade show alert-danger" role="alert">
                <span id="feedbackText">{{ session('error') }}</span>
                <button type="button" class="btn-close" onclick="hideFeedback()"></button>
            </div>
        @endif
        <div id="feedbackMessage" class="alert alert-dismissible fade show d-none" role="alert">
            <span id="feedbackText"></span>
            <button type="button" class="btn-close" onclick="hideFeedback()"></button>
        </div>

        <!-- Absensi Table -->
        <div class="mb-4">
            <h4 style="font-size: 18px; font-weight: 600; color: #333;">
                Absensi Kelas {{ $selectedClass }} pada {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('l, d F Y') }}
            </h4>

            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                th {
                    background-color: #f2f2f2;
                }
                .status-display {
                    display: block;
                }
                .status-desc {
                    position: relative;
                }
            </style>

            <table>
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Status Kehadiran</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensiRecords as $record)
                        <tr>
                            <td>{{ $record->nim ?? 'N/A' }}</td>
                            <td>{{ $record->mahasiswa->nama ?? 'Unknown' }}</td>
                            <td>
                                <div class="status-display">
                                    <span class="selected-status">
                                        @if ($record->status_kehadiran === 'hadir')
                                            ✅ Hadir
                                        @elseif ($record->status_kehadiran === 'alpa')
                                            ❌ Alpa
                                        @elseif ($record->status_kehadiran === 'izin')
                                            📝 Izin
                                        @else
                                            Tidak Diketahui
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="status-desc">
                                <span class="keterangan-text">
                                    {{ $record->keterangan ?? '' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Tidak ada data absensi untuk perwalian ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Berita Acara Form -->
        <div class="text-center">
            <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">
            <h3 class="fw-bold title-centered">AGENDA PERWALIAN</h3>

            <form id="beritaAcaraForm" onsubmit="handleFormSubmit(event)">
                @csrf
                <div class="text-start mt-4">
                    <div class="info-container">
                        <div class="info-row">
                            <strong class="info-label-large">Kelas</strong><span>:</span>
                            <input type="text" name="kelas" value="{{ $selectedClass }}" class="editable-input" readonly>
                        </div>
                        <div class="info-row">
                            <strong class="info-label-large">Angkatan</strong><span>:</span>
                            <input type="number" name="angkatan" value="{{ $selectedAngkatan ?? '' }}" class="editable-input" readonly>
                        </div>
                        <div class="info-row">
                            <strong class="info-label-large">Dosen Wali</strong><span>:</span>
                            <strong>{{ session('user')['nama'] ?? 'Nama Tidak Ditemukan' }}</strong>
                        </div>
                    </div>
                </div>
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

                <div class="footer-info">
                    <span class="left">IT Del/Berita Acara Perwalian</span>
                    <span class="right">Halaman 1 dari 2</span>
                </div>

                <div class="page-break"></div>

                <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">
                <h3 class="fw-bold title-centered">BERITA ACARA PERWALIAN</h3>
                <h5 class="sub-title">( Feedback dari mahasiswa selama perwalian )</h5>

                <div class="berita-acara-box">
                    <div class="info-container">
                        <div class="info-row">
                            <span class="info-label">Hari/Tanggal</span><span>:</span>
                            <input type="date" name="hari_tanggal" value="{{ old('hari_tanggal') }}" class="editable-input" required oninput="showFieldFeedback('Hari/Tanggal Feedback', this)">
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

                <div class="signature-box">
                    <p>
                        Sitoluama, <input type="date" name="tanggal_ttd" value="{{ old('tanggal_ttd') }}" class="editable-input" required oninput="showFieldFeedback('Tanggal Tanda Tangan', this)">
                    </p>
                    <br><br><br>
                    <p><input type="text" name="dosen_wali_ttd" value="({{ session('user')['nama'] ?? 'Nama Tidak Ditemukan' }})" class="editable-input" required oninput="showFieldFeedback('Dosen Wali TTD', this)"></p>
                </div>

                <div class="footer-info">
                    <span class="left">IT Del/Berita Acara Perwalian</span>
                    <span class="right">Halaman 2 dari 2</span>
                </div>

                <div class="submit-container">
                    <button type="submit" class="btn btn-success" id="submitButton">
                        <span id="buttonText">Submit</span>
                        <span id="buttonLoading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .border-bottom-line { border-bottom: 1px solid #E5E5E5; padding-bottom: 10px; }
        #feedbackMessage.alert-success { background-color: #d4edda; color: #155724; }
        #feedbackMessage.alert-danger { background-color: #f8d7da; color: #721c24; }
        .berita-acara-box { min-height: 700px; border: 2px solid #333; border-radius: 5px; padding: 15px; background-color: #f9f9f9; }
        .info-container { display: grid; grid-template-columns: max-content 10px auto; gap: 5px; }
        .info-row { display: contents; }
        .info-label-large { font-size: 1.2rem; font-weight: bold; text-align: left; white-space: nowrap; }
        .info-label { font-size: 1rem; font-weight: normal; text-align: left; white-space: nowrap; }
        .info-row span { align-self: start; }
        .title-centered { display: flex; justify-content: center; align-items: center; min-height: 100px; }
        .footer-info { display: flex; justify-content: space-between; margin-top: 10px; font-size: 0.9rem; font-weight: bold; }
        .footer-info .left { text-align: left; }
        .footer-info .right { text-align: right; }
        .page-break { page-break-before: always; margin-top: 100px; }
        .signature-box { margin-top: 40px; text-align: left; }
        .signature-box p { margin: 5px 0; font-size: 1rem; font-weight: bold; }
        .submit-container { display: flex; justify-content: flex-end; padding-right: 0px; margin-top: 50px;}

        .editable-input, .editable-textarea { border: none; background: transparent; outline: none; width: 100%; font-size: inherit; font-family: inherit; }
        .editable-textarea { min-height: 50px; resize: none; }
        
    
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status-display {
            display: block;
        }
        .status-desc {
            position: relative;
        }
        /* Back button styling from perwalian kelas */
        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            background-color: #68B8EA;
            color: #fff;
            font-size: 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 30px;
            margin-top: -4px;
            margin-left: 10px;
        }
        .back-btn:hover {
            background-color: #4A9CD6;
        }
        .back-btn .arrow {
            font-size: 40px;
            margin-right: 0px;
            line-height: 1;
        }
    </style>

    <script>
        const storeRoute = "{{ route('berita_acara.store', ['date' => $selectedDate, 'class' => $selectedClass]) }}";
        const successRoute = "{{ route('berita_acara.success', ['kelas' => $selectedClass, 'tanggal_perwalian' => $selectedDate]) }}";

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
                const message = element.type === 'date'
                    ? `${fieldName} diisi: ${new Date(element.value).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })}`
                    : `${fieldName} diisi: ${element.value}`;
                showFeedback(message, 'success');
            } else {
                showFeedback(`${fieldName} dikosongkan.`, 'danger');
            }
        }

        function handleFormSubmit(event) {
            event.preventDefault();
            const form = document.getElementById('beritaAcaraForm');
            const submitButton = document.getElementById('submitButton');
            const buttonText = document.getElementById('buttonText');
            const buttonLoading = document.getElementById('buttonLoading');

            submitButton.disabled = true;
            buttonText.classList.add('d-none');
            buttonLoading.classList.remove('d-none');

            const formData = new FormData(form);

            // Log the form data for debugging
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }

            fetch(storeRoute, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                submitButton.disabled = false;
                buttonText.classList.remove('d-none');
                buttonLoading.classList.add('d-none');

                if (data.success) {
                    // Redirect to the success page
                    window.location.href = successRoute;
                } else {
                    showFeedback(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                submitButton.disabled = false;
                buttonText.classList.remove('d-none');
                buttonLoading.classList.add('d-none');
                showFeedback('Gagal mengirimkan form: ' + error.message, 'danger');
            });
        }

        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = "{{ route('logout') }}";
            }
        }

        function goBack() {
            window.history.back();
        }
    </script>
@endsection