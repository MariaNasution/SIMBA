@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <!-- Search Filters Section -->
    <div class="row align-items-end">
        <!-- Dropdown Pilih Prodi -->
        <div class="col-md-3">
            <label for="pilihProdi" class="form-label">Pilih Prodi</label>
            <select class="form-select custom-select" id="pilihProdi" name="prodi">
                <option selected value="">Pilih Prodi</option>
                <option value="S1Informatika">S1 Informatika</option>
                <option value="S1SistemInformasi">S1 Sistem Informasi</option>
                <option value="S1TeknikElektro">S1 Teknik Elektro</option>
                <option value="S1TeknikInformasi">S1 Teknik Informasi</option>
                <option value="S1TeknikKomputer">S1 Teknik Komputer</option>
                <option value="S1TeknikRekayasaPerangkatLunak">S1 Teknik Rekayasa Perangkat Lunak</option>
                <option value="S1ManajemenRekayasa">S1 Manajemen Rekayasa</option>
                <option value="S1TeknikMetalurgi">S1 Teknik Metalurgi</option>
                <option value="S1TeknikBioproses">S1 Teknik Bioproses</option>
            </select>
        </div>

        <!-- Dropdown Keterangan -->
        <div class="col-md-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <select class="form-select custom-select" id="keterangan" name="keterangan">
                <option selected value="">Keterangan</option>
                <option value="Semester Baru">Semester Baru</option>
                <option value="Sebelum UTS">Sebelum UTS</option>
                <option value="Sebelum UAS">Sebelum UAS</option>
            </select>
        </div>

        <!-- Dropdown Angkatan -->
        <div class="col-md-3">
            <label for="angkatan" class="form-label">Angkatan</label>
            <select class="form-select custom-select" id="angkatan" name="angkatan">
                <option selected value="">Angkatan</option>
                @for ($year = 2014; $year <= date('Y'); $year++)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endfor
            </select>
        </div>

        <!-- Tombol Terapkan -->
        <div class="col-md-3">
            <button class="btn btn-primary w-100 custom-btn" onclick="searchBeritaAcara(event)">Terapkan</button>
        </div>
    </div>

    <!-- Search Results -->
    <div id="searchResults" class="mt-5 d-none">
        <h2>Hasil Pencarian</h2>
        <div id="resultsContainer"></div>
    </div>
</div>

<style>
    .custom-select {
        background-color: #F5F5F5;
        border: 1px solid #E0E0E0;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
        color: #757575;
    }
    .custom-btn {
        background-color: #2E7D32;
        border: none;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
    }
    .custom-btn:hover {
        background-color: #1B5E20;
    }
    .form-label {
        font-size: 14px;
        color: #424242;
        margin-bottom: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    th, td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    th {
        background-color: #f2f2f2;
        font-weight: 600;
        color: #333;
    }
    .berita-acara-box {
        min-height: 300px;
        border: 2px solid #333;
        border-radius: 5px;
        padding: 15px;
        background-color: #f9f9f9;
        margin-bottom: 30px;
    }
    .info-container {
        display: grid;
        grid-template-columns: max-content 10px auto;
        gap: 5px;
        margin-bottom: 15px;
    }
    .info-row {
        display: contents;
    }
    .info-label-large {
        font-size: 1.2rem;
        font-weight: bold;
        color: #333;
    }
    .info-label {
        font-size: 1rem;
        color: #555;
    }
    .title-centered {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100px;
        color: #2E7D32;
        font-weight: bold;
    }
    .sub-title {
        text-align: center;
        color: #555;
        margin-bottom: 20px;
    }
    .footer-info {
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
        font-size: 0.9rem;
        font-weight: bold;
        color: #666;
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
    .record-container {
        margin-bottom: 40px;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    .class-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2E7D32;
        margin-bottom: 20px;
        text-align: center;
    }
    .editable-input, .editable-textarea {
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
</style>

<script>
    const searchRoute = "{{ route('kemahasiswaan_perwalian.berita_acara.search') }}";

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    function searchBeritaAcara(event) {
        event.preventDefault();

        const prodi = document.getElementById('pilihProdi').value;
        const keterangan = document.getElementById('keterangan').value;
        const angkatan = document.getElementById('angkatan').value;

        if (!prodi || !keterangan || !angkatan) {
            alert('Silakan pilih semua filter (Prodi, Keterangan, Angkatan).');
            return;
        }

        const searchResults = document.getElementById('searchResults');
        const resultsContainer = document.getElementById('resultsContainer');
        resultsContainer.innerHTML = '';
        searchResults.classList.add('d-none');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            resultsContainer.innerHTML = '<p>Gagal: CSRF token tidak ditemukan.</p>';
            searchResults.classList.remove('d-none');
            return;
        }

        fetch(searchRoute, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ prodi, keterangan, angkatan }),
        })
        .then(response => response.json().then(data => ({ response, data })))
        .then(({ response, data }) => {
            if (!response.ok) {
                throw new Error(data.message || 'HTTP error');
            }

            if (!data.success || !data.data) {
                throw new Error('Invalid response data');
            }

            const { perwalian, absensi = [], berita_acara } = data.data;

            if (berita_acara.length === 0) {
                resultsContainer.innerHTML = '<p>Tidak ada Berita Acara yang ditemukan.</p>';
                searchResults.classList.remove('d-none');
                return;
            }

            berita_acara.forEach((berita) => {
                // Filter absensi by kelas
                const relatedAbsensi = absensi.filter(a => a.kelas === berita.kelas);
                const recordDiv = document.createElement('div');
                recordDiv.classList.add('record-container');
                recordDiv.innerHTML = `
                    <h3 class="class-title">Berita Acara Kelas ${berita.kelas || 'N/A'}</h3>

                    <!-- Absensi Table -->
                    <div class="mb-4">
                        <h4 style="font-size: 18px; font-weight: 600; color: #333;">
                            Absensi Kelas ${berita.kelas || 'N/A'} pada ${formatDate(berita.tanggal_perwalian)}
                        </h4>
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
                                ${relatedAbsensi.length > 0 ? relatedAbsensi.map(absensi => `
                                    <tr>
                                        <td>${absensi.nim || 'N/A'}</td>
                                        <td>${absensi.nama || 'Unknown'}</td>
                                        <td>${absensi.status_kehadiran === 'hadir' ? '✅ Hadir' : absensi.status_kehadiran === 'alpa' ? '❌ Alpa' : absensi.status_kehadiran === 'izin' ? '📝 Izin' : 'Tidak Diketahui'}</td>
                                        <td>${absensi.keterangan || ''}</td>
                                    </tr>
                                `).join('') : '<tr><td colspan="4">Tidak ada data absensi.</td></tr>'}
                            </tbody>
                        </table>
                    </div>

                    <!-- Berita Acara Details -->
                    <div class="text-center">
                        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" style="max-width: 150px;">
                        <h3 class="fw-bold title-centered">AGENDA PERWALIAN</h3>
                        <div class="text-start mt-4">
                            <div class="info-container">
                                <div class="info-row">
                                    <strong class="info-label-large">Kelas</strong><span>:</span>
                                    <span class="editable-input">${berita.kelas || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <strong class="info-label-large">Angkatan</strong><span>:</span>
                                    <span class="editable-input">${berita.angkatan || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <strong class="info-label-large">Dosen Wali</strong><span>:</span>
                                    <strong>${berita.dosen_wali || 'N/A'}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="berita-acara-box">
                            <div class="info-container">
                                <div class="info-row">
                                    <span class="info-label">Tanggal</span><span>:</span>
                                    <span class="editable-input">${formatDate(berita.tanggal_perwalian)}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Perihal</span><span>:</span>
                                    <span class="editable-input">${berita.perihal_perwalian || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Agenda</span><span>:</span>
                                    <p class="editable-textarea">${berita.agenda_perwalian || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="footer-info">
                            <span class="left">IT Del/Berita Acara Perwalian</span>
                            <span class="right">Halaman 1 dari 2</span>
                        </div>

                        <div class="page-break"></div>

                        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" style="max-width: 150px;">
                        <h3 class="fw-bold title-centered">BERITA ACARA PERWALIAN</h3>
                        <h5 class="sub-title">(Feedback dari mahasiswa selama perwalian)</h5>
                        <div class="berita-acara-box">
                            <div class="info-container">
                                <div class="info-row">
                                    <span class="info-label">Hari/Tanggal</span><span>:</span>
                                    <span class="editable-input">${formatDate(berita.hari_tanggal_feedback)}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Perihal Feedback</span><span>:</span>
                                    <span class="editable-input">${berita.perihal_feedback || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Catatan</span><span>:</span>
                                    <p class="editable-textarea">${berita.catatan_feedback || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="signature-box">
                            <p>Sitoluama, <span class="editable-input">${formatDate(berita.tanggal_ttd)}</span></p>
                            <br><br><br>
                            <p>${berita.dosen_wali_ttd || 'N/A'}</p>
                        </div>
                        <div class="footer-info">
                            <span class="left">IT Del/Berita Acara Perwalian</span>
                            <span class="right">Halaman 2 dari 2</span>
                        </div>
                    </div>
                `;
                resultsContainer.appendChild(recordDiv);
            });
            searchResults.classList.remove('d-none');
        })
        .catch(error => {
            console.error('Error:', error);
            resultsContainer.innerHTML = `<p>Gagal melakukan pencarian: ${error.message}. Silakan coba lagi.</p>`;
            searchResults.classList.remove('d-none');
        });
    }
</script>
@endsection