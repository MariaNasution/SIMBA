<div class="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
        <div class="header-text">
            <h4>SIMBA</h4>
            <p>Dosen</p>
        </div>
    </div>
    <div class="profile-section">
        <div class="profile-card">
            <a href="{{ route('profil') }}">
                <img src="{{ asset('assets/img/profil.jpg') }}" alt="Profile Picture" class="profile-picture">
                <h4 class="profile-name">
                    {{ session('user')['username'] ?? 'Nama Tidak Ditemukan' }}
                </h4>
                <p class="profile-id">
                    Dosen Wali
                </p>
            </a>
        </div>
    </div>
    <ul class="menu">
        <li class="menu-item">
            <a href="{{ route('dosen') }}">
                <i class="fas fa-home"></i> Beranda
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('set.perwalian') }}">
                <i class="fas fa-users"></i> Set Perwalian
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('absensi') }}">
                <i class="fas fa-check-square"></i> Absensi Mahasiswa
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('berita_acara.select_class') }}">
                <i class="fas fa-check-square"></i> Berita Acara
            </a>
        </li>
        <!-- Tombol Histori -->
        <li class="menu-item">
            <a href="{{ route('dosen.histori') }}">
                <i class="fas fa-history"></i> Histori
            </a>
        </li>
    </ul>
</div>
