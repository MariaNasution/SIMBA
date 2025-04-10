@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mt-4">
        <!-- Dropdown Pilih Prodi -->
        <div class="col-md-3">
            <label for="pilihProdi" class="form-label">Pilih Prodi</label>
            <select class="form-select custom-select" id="pilihProdi" name="prodi">
                <option selected value="Pilih Prodi">Pilih Prodi</option>
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
                <option selected value="Keterangan">Keterangan</option>
                <option value="Semester Baru">Semester Baru</option>
                <option value="Sebelum UTS">Sebelum UTS</option>
                <option value="Sebelum UAS">Sebelum UAS</option>
            </select>
        </div>

        <!-- Dropdown Angkatan -->
        <div class="col-md-3">
            <label for="angkatan" class="form-label">Angkatan</label>
            <select class="form-select custom-select" id="angkatan" name="angkatan">
                <option selected value="Angkatan">Angkatan</option>
                <option>2014</option>
                <option>2015</option>
                <option>2016</option>
                <option>2017</option>
                <option>2018</option>
                <option>2019</option>
                <option>2020</option>
                <option>2021</option>
                <option>2022</option>
                <option>2023</option>
                <option>2024</option>
                <option>2025</option>
            </select>
        </div>

        <!-- Tombol Terapkan sejajar dengan dropdown -->
        <div class="col-md-3">
            <div class="w-100">
                <label class="form-label invisible">Terapkan</label>
                <button class="btn btn-primary w-100 custom-btn" onclick="searchBeritaAcara(event)">Terapkan</button>
            </div>
        </div>
    </div>

    <!-- Search Results -->
    <div id="searchResults" class="mt-5 d-none">
        <h2>Hasil Pencarian</h2>
        <div id="resultsContainer"></div>
    </div>
</div>

<style>
    /* Custom styling untuk dropdown */
    .custom-select {
        background-color: #F5F5F5;
        border: 1px solid #E0E0E0;
        border-radius: 5px;
        padding: 10px;
        font-size: 14px;
        color: #757575;
    }

    .custom-select option {
        background-color: #E8F5E9;
        color: #424242;
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

    /* Styling for search results */
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
    td {
        color: #555;
    }
    .status-display {
        display: block;
    }
    .status-desc {
        position: relative;
    }
    .berita-acara-box {
        min-height: 300px;
        border: 2px solid #333;
        border-radius: 8px;
        padding: 20px;
        background-color: #f9f9f9;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .info-container {
        display: grid;
        grid-template-columns: max-content 10px auto;
        gap: 10px;
        margin-bottom: 15px;
    }
    .info-row {
        display: contents;
    }
    .info-label-large {
        font-size: 1.2rem;
        font-weight: bold;
        text-align: left;
        white-space: nowrap;
        color: #333;
    }
    .info-label {
        font-size: 1rem;
        font-weight: normal;
        text-align: left;
        white-space: nowrap;
        color: #555;
    }
    .info-row span, .info-row p {
        align-self: start;
        color: #444;
    }
    .info-row p {
        margin: 0;
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
        margin-top: 20px;
        font-size: 0.9rem;
        font-weight: bold;
        color: #666;
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
        color: #333;
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
</style>

<script>
// Define the search route
const searchRoute = "{{ route('kemahasiswaan_perwalian.berita_acara.search') }}";
console.log('Search Route Initialized:', searchRoute);

// Function to log objects in a readable format
function logObject(label, obj) {
    console.log(label + ':', JSON.parse(JSON.stringify(obj)));
}

// Function to format dates to "Selasa, 2 Maret 2025"
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
    console.log('Starting searchBeritaAcara function at:', new Date().toISOString());

    // Get filter inputs
    const prodi = document.getElementById('pilihProdi').value;
    const keterangan = document.getElementById('keterangan').value;
    const angkatan = document.getElementById('angkatan').value;

    // Log input values
    logObject('Search Inputs', { prodi, keterangan, angkatan });

    // Validate inputs
    if (prodi === 'Pilih Prodi' || keterangan === 'Keterangan' || angkatan === 'Angkatan') {
        console.log('Validation failed: One or more filters not selected');
        alert('Silakan pilih semua filter (Prodi, Keterangan, Angkatan) untuk mencari Berita Acara.');
        return;
    }

    // Prepare UI for results
    const searchResults = document.getElementById('searchResults');
    const resultsContainer = document.getElementById('resultsContainer');
    resultsContainer.innerHTML = ''; // Clear previous results
    searchResults.classList.add('d-none'); // Hide until results are loaded
    console.log('UI prepared: Results container cleared, searchResults hidden');

    // Get the CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('CSRF token not found in meta tag');
        resultsContainer.innerHTML = '<p>Gagal melakukan pencarian: CSRF token tidak ditemukan.</p>';
        searchResults.classList.remove('d-none');
        return;
    }
    console.log('CSRF Token Retrieved:', csrfToken);

    // Prepare the request body
    const requestBody = JSON.stringify({
        prodi: prodi,
        keterangan: keterangan,
        angkatan: angkatan,
    });
    logObject('Request Body', requestBody);

    // Prepare the headers
    const headers = {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Content-Type': 'application/json',
    };
    logObject('Request Headers', headers);

    // Log before sending the fetch request
    console.log('Sending fetch request to:', searchRoute);

    fetch(searchRoute, {
        method: 'POST',
        headers: headers,
        body: requestBody,
    })
    .then(response => {
        // Log response details
        console.log('Fetch Response Status:', response.status);
        logObject('Fetch Response Headers', [...response.headers.entries()]);

        // Always parse the response as JSON
        return response.json().then(data => {
            response.data = data;
            return response;
        });
    })
    .then(response => {
        if (!response.ok) {
            logObject('Response Error Body', response.data);
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = response.data;
        logObject('Fetch Response Data', data);

        if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
            console.log('Processing search results, count:', data.data.length);
            data.data.forEach((record, index) => {
                console.log(`Processing record ${index + 1}:`, record);
                const recordDiv = document.createElement('div');
                recordDiv.classList.add('record-container');

                // Add a title for the class
                recordDiv.innerHTML = `
                    <h3 class="class-title">Berita Acara Kelas ${record.kelas || 'N/A'}</h3>

                    <!-- Absensi Table -->
                    <div class="mb-4">
                        <h4 style="font-size: 18px; font-weight: 600; color: #333;">
                            Absensi Kelas ${record.kelas || 'N/A'} pada ${formatDate(record.tanggal_perwalian)}
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
                                ${record.absensi && Array.isArray(record.absensi) && record.absensi.length > 0 ? record.absensi.map((absensi, absensiIndex) => {
                                    console.log(`Processing absensi ${absensiIndex + 1} for record ${index + 1}:`, absensi);
                                    return `
                                        <tr>
                                            <td>${absensi.nim || 'N/A'}</td>
                                            <td>${absensi.nama || 'Unknown'}</td>
                                            <td>
                                                <div class="status-display">
                                                    <span class="selected-status">
                                                        ${absensi.status_kehadiran === 'hadir' ? '✅ Hadir' :
                                                          absensi.status_kehadiran === 'alpa' ? '❌ Alpa' :
                                                          absensi.status_kehadiran === 'izin' ? '📝 Izin' : 'Tidak Diketahui'}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="status-desc">
                                                <span class="keterangan-text">${absensi.keterangan || ''}</span>
                                            </td>
                                        </tr>
                                    `;
                                }).join('') : `
                                    <tr>
                                        <td colspan="4">Tidak ada data absensi untuk perwalian ini.</td>
                                    </tr>
                                `}
                            </tbody>
                        </table>
                    </div>

                    <!-- Berita Acara Details -->
                    <div class="text-center">
                        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">
                        <h3 class="fw-bold title-centered">AGENDA PERWALIAN</h3>
                        <div class="text-start mt-4">
                            <div class="info-container">
                                <div class="info-row">
                                    <strong class="info-label-large">Kelas</strong><span>:</span>
                                    <span>${record.kelas || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <strong class="info-label-large">Angkatan</strong><span>:</span>
                                    <span>${record.angkatan || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <strong class="info-label-large">Dosen Wali</strong><span>:</span>
                                    <strong>${record.dosen_wali || 'N/A'}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="berita-acara-box">
                            <div class="info-container">
                                <div class="info-row">
                                    <span class="info-label">Tanggal</span><span>:</span>
                                    <span>${formatDate(record.tanggal_perwalian)}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Perihal</span><span>:</span>
                                    <span>${record.perihal_perwalian || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Agenda</span><span>:</span>
                                    <p>${record.agenda_perwalian || 'N/A'}</p>
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
                        <h5 class="sub-title">(Feedback dari mahasiswa selama perwalian)</h5>
                        <div class="berita-acara-box">
                            <div class="info-container">
                                <div class="info-row">
                                    <span class="info-label">Hari/Tanggal</span><span>:</span>
                                    <span>${formatDate(record.hari_tanggal_feedback)}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Perihal Feedback</span><span>:</span>
                                    <span>${record.perihal_feedback || 'N/A'}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Catatan</span><span>:</span>
                                    <p>${record.catatan_feedback || 'N/A'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="signature-box">
                            <p>Sitoluama, ${formatDate(record.tanggal_ttd)}</p>
                            <br><br><br>
                            <p>${record.dosen_wali_ttd || 'N/A'}</p>
                        </div>
                        <div class="footer-info">
                            <span class="left">IT Del/Berita Acara Perwalian</span>
                            <span class="right">Halaman 2 dari 2</span>
                        </div>
                    </div>
                `;
                resultsContainer.appendChild(recordDiv);
                console.log(`Record ${index + 1} appended to resultsContainer`);
            });
            searchResults.classList.remove('d-none');
            console.log('Search results displayed');
        } else {
            logObject('No results found', data);
            resultsContainer.innerHTML = '<p>Tidak ada Berita Acara yang ditemukan dengan kriteria tersebut.</p>';
            searchResults.classList.remove('d-none');
            console.log('No results displayed');
        }
    })
    .catch(error => {
        console.error('Fetch Error:', error.message);
        console.error('Error Stack:', error.stack);
        resultsContainer.innerHTML = `<p>Gagal melakukan pencarian: ${error.message}. Silakan coba lagi.</p>`;
        searchResults.classList.remove('d-none');
        console.log('Error displayed to user');
    })
    .finally(() => {
        console.log('searchBeritaAcara function completed at:', new Date().toISOString());
    });
}
</script>
@endsection