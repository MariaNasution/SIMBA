@extends('layouts.app')

@section('content')
<div class="container mt-4">
    
    <div class="row mt-4">
        <!-- Dropdown Pilih Prodi -->
        <div class="col-md-3">
            <label for="pilihProdi" class="form-label">Pilih Prodi</label>
            <select class="form-select custom-select" id="pilihProdi" name="prodi">
                <option selected value="Pilih Prodi">Pilih Prodi</option>
                <option value="S1 Informatika">S1 Informatika</option>
                <option value="S1 Sistem Informasi">S1 Sistem Informasi</option>
                <option value="S1 Teknik Elektro">S1 Teknik Elektro</option>
                <option value="D3 Teknologi Informasi">D3 Teknologi Informasi</option>
                <option value="D3 Teknologi Komputer">D3 Teknologi Komputer</option>
                <option value="D4 Teknologi Rekayasa Perangkat Lunak">D4 Teknologi Rekayasa Perangkat Lunak</option>
                <option value="S1 Manajemen Rekayasa">S1 Manajemen Rekayasa</option>
                <option value="S1 Teknik Metalurgi">S1 Teknik Metalurgi</option>
                <option value="S1 Bioproses">S1 Bioproses</option>
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
    .berita-acara-box {
        min-height: 300px;
        border: 2px solid #333;
        border-radius: 5px;
        padding: 15px;
        background-color: #f9f9f9;
        margin-bottom: 20px;
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
</style>

<script>
    const searchRoute = "{{ route('kemahasiswaan_perwalian.berita_acara.search') }}";

    function searchBeritaAcara(event) {
        event.preventDefault();

        // Log the start of the search function
        console.log('Starting searchBeritaAcara function');

        const prodi = document.getElementById('pilihProdi').value;
        const keterangan = document.getElementById('keterangan').value;
        const angkatan = document.getElementById('angkatan').value;

        // Log the input values
        console.log('Search Inputs:', {
            prodi: prodi,
            keterangan: keterangan,
            angkatan: angkatan
        });

        // Validate inputs
        if (prodi === 'Pilih Prodi' && keterangan === 'Keterangan' && angkatan === 'Angkatan') {
            console.log('Validation failed: No filters selected');
            alert('Silakan pilih setidaknya satu filter untuk mencari Berita Acara.');
            return;
        }

        const searchResults = document.getElementById('searchResults');
        const resultsContainer = document.getElementById('resultsContainer');
        resultsContainer.innerHTML = ''; // Clear previous results
        searchResults.classList.add('d-none'); // Hide until results are loaded

        // Log the search route
        console.log('Search Route:', searchRoute);

        // Get the CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token not found in meta tag');
            resultsContainer.innerHTML = '<p>Gagal melakukan pencarian: CSRF token tidak ditemukan.</p>';
            searchResults.classList.remove('d-none');
            return;
        }
        console.log('CSRF Token:', csrfToken);

        // Prepare the request body
        const requestBody = JSON.stringify({
            prodi: prodi,
            keterangan: keterangan,
            angkatan: angkatan,
        });
        console.log('Request Body:', requestBody);

        // Prepare the headers
        const headers = {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
        };
        console.log('Request Headers:', headers);

        // Log before sending the fetch request
        console.log('Sending fetch request to:', searchRoute);

        fetch(searchRoute, {
            method: 'POST',
            headers: headers,
            body: requestBody,
        })
        .then(response => {
            // Log the response status and headers
            console.log('Fetch Response Status:', response.status);
            console.log('Fetch Response Headers:', [...response.headers.entries()]);

            // Check if the response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            return response.json();
        })
        .then(data => {
            console.log('Fetch Response Data:', data);

            if (data.success && data.data.length > 0) {
                data.data.forEach(record => {
                    const recordDiv = document.createElement('div');
                    recordDiv.classList.add('mt-4');

                    // Absensi Table
                    recordDiv.innerHTML = `
                        <h4 style="font-size: 18px; font-weight: 600; color: #333;">
                            Absensi Kelas ${record.kelas} pada ${record.tanggal_perwalian}
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
                                ${record.absensi.length > 0 ? record.absensi.map(absensi => `
                                    <tr>
                                        <td>${absensi.nim}</td>
                                        <td>${absensi.nama}</td>
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
                                            <span class="keterangan-text">${absensi.keterangan}</span>
                                        </td>
                                    </tr>
                                `).join('') : `
                                    <tr>
                                        <td colspan="4">Tidak ada data absensi untuk perwalian ini.</td>
                                    </tr>
                                `}
                            </tbody>
                        </table>

                        <!-- Berita Acara Details -->
                        <div class="text-center mt-5">
                            <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo IT Del" class="mb-4" style="max-width: 150px;">
                            <h3 class="fw-bold title-centered">AGENDA PERWALIAN</h3>
                            <div class="text-start mt-4">
                                <div class="info-container">
                                    <div class="info-row">
                                        <strong class="info-label-large">Kelas</strong><span>:</span>
                                        <span>${record.kelas}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong class="info-label-large">Angkatan</strong><span>:</span>
                                        <span>${record.angkatan}</span>
                                    </div>
                                    <div class="info-row">
                                        <strong class="info-label-large">Dosen Wali</strong><span>:</span>
                                        <strong>${record.dosen_wali}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="berita-acara-box">
                                <div class="info-container">
                                    <div class="info-row">
                                        <span class="info-label">Tanggal</span><span>:</span>
                                        <span>${record.tanggal_perwalian}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Perihal</span><span>:</span>
                                        <span>${record.perihal_perwalian}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Agenda</span><span>:</span>
                                        <p>${record.agenda_perwalian}</p>
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
                                        <span>${record.hari_tanggal_feedback}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Perihal Feedback</span><span>:</span>
                                        <span>${record.perihal_feedback}</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Catatan</span><span>:</span>
                                        <p>${record.catatan_feedback}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="signature-box">
                                <p>Sitoluama, ${record.tanggal_ttd}</p>
                                <br><br><br>
                                <p>${record.dosen_wali_ttd}</p>
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
            } else {
                console.log('No results found:', data);
                resultsContainer.innerHTML = '<p>Tidak ada Berita Acara yang ditemukan dengan kriteria tersebut.</p>';
                searchResults.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            console.error('Error Stack:', error.stack);
            resultsContainer.innerHTML = '<p>Gagal melakukan pencarian. Silakan coba lagi.</p>';
            searchResults.classList.remove('d-none');
        });
    }
</script>
@endsection