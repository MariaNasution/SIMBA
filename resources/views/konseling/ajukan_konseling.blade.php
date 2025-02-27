@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Header dan Logout --}}
        <div class="d-flex align-items-center mb-4 border-bottom-line">
            <h3 class="me-auto">
                <a href="{{ route('admin') }}"><i class="fas fa-user-friends me-3"></i>Home</a> /
                <a href="{{ route('riwayat_konseling') }}">Ajukan Konseling</a>
            </h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
            </a>
        </div>

        {{-- Judul --}}
        <h5 class="header-title text-primary mb-4">Mahasiswa Aktif TA 2024</h5>
    
    {{-- Form Pencarian Mahasiswa --}}
        <form action="{{ route('konseling.cari') }}" method="GET">
            @csrf
            <div class="col-md-6">
                <div class="mb-2 row">
                    <label class="col-sm-2 col-form-label fw-bold">NIM</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="nim" name="nim" value="{{ $nim ?? '' }}" required>
                        </div>
                </div>
            </div>
</br>
        {{-- Tombol --}}
        <div class="text-center">
            <button type="submit" class="btn btn-custom-blue">Cari</button>
            <button type="button" id="resetButton" class="btn btn-secondary">Hapus</button>
        </div>
    </form>
    
    {{-- Menampilkan Error --}}
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- Tabel Data Mahasiswa --}}
    @if (!empty($dataMahasiswa))
        <div class="mt-4">
            <h4>Data Mahasiswa</h4>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>Tahun Masuk</th>
                        <th>Program Studi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $dataMahasiswa['nim'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['nama'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['tahun_masuk'] ?? '-' }}</td>
                        <td>{{ $dataMahasiswa['prodi'] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
    {{-- Form untuk mengajukan konseling --}}
        <form action="{{ route('konseling.ajukan') }}" method="GET">
            @csrf
            <input type="hidden" name="nim" value="{{ $dataMahasiswa['nim'] ?? '' }}">
            
            {{-- Waktu Konseling --}}
            <h6 class="mt-4 text-start">Waktu Konseling</h6>
            <div class="d-flex justify-content-start align-items-center mb-3">
                <input type="date" name="tanggal_konseling" class="form-control flex-grow-1 date-input" required>
                <div class="ms-2">
                    <button type="button" class="btn btn-danger btn-sm clear-date"><i class="fas fa-times"></i></button>
                    <button type="button" class="btn btn-secondary btn-sm ms-1 show-time"><i class="fas fa-clock"></i></button>
                </div>
            </div>
            
            <div class="time-selector mb-3" style="display: none;">
                <select name="waktu_konseling" class="form-select">
                    <option value="08:00">08:00</option>
                    <option value="09:00">09:00</option>
                    <option value="10:00">10:00</option>
                    <option value="11:00">11:00</option>
                    <option value="13:00">13:00</option>
                    <option value="14:00">14:00</option>
                    <option value="15:00">15:00</option>
                </select>
            </div>
            
            {{-- Tombol Konfirmasi --}}
            <div class="d-flex justify-content-center mt-4">
                <button type="submit" class="btn btn-custom-blue btn-lg px-4 me-2">Buat</button>
                <a href="{{ route('beranda') }}" class="btn btn-secondary btn-lg px-4">Batal</a>
            </div>
        </form> 
</div>

{{-- SweetAlert untuk Logout --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Apakah anda yakin ingin keluar?',
            text: "Anda akan keluar dari akun ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, keluar!',
            cancelButtonText: 'Tidak',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route('logout') }}';
            }
        });
    }
    
    // Toggle time selector
    document.addEventListener('DOMContentLoaded', function() {
        const showTimeBtn = document.querySelector('.show-time');
        const timeSelector = document.querySelector('.time-selector');
        const clearDateBtn = document.querySelector('.clear-date');
        const dateInput = document.querySelector('.date-input');
        
        if (showTimeBtn) {
            showTimeBtn.addEventListener('click', function() {
                timeSelector.style.display = timeSelector.style.display === 'none' ? 'block' : 'none';
            });
        }
        
        if (clearDateBtn) {
            clearDateBtn.addEventListener('click', function() {
                dateInput.value = '';
            });
        }
   // Fungsi reset untuk tombol Hapus
   const resetButton = document.getElementById('resetButton');
        const nimInput = document.getElementById('nim');
        const mahasiswaData = document.getElementById('mahasiswaData');
        
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                // Reset form input
                nimInput.value = '';
                
                // Sembunyikan data mahasiswa jika ada
                if (mahasiswaData) {
                    mahasiswaData.classList.add('d-none');
                }
                
                // Fokus kembali ke input NIM
                nimInput.focus();
            });
        }
    });
</script>
@endsection