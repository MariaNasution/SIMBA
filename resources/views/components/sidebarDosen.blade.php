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
            <a href="{{ route('dosen.perwalian') }}">
                <i class="fas fa-users"></i> Set Perwalian
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('dosen.presensi') }}">
                <i class="fas fa-check-square"></i> Presensi
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('bursar') }}">
                <i class="fas fa-wallet"></i> Berita Acara
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('bursar') }}">
                <i class="fas fa-wallet"></i> History
            </a>
        </li>
    </ul>
</div>