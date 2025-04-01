<div class="sidebar bg-dark text-light vh-100">
    <div class="sidebar-header">
        <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
        <div class="header-text">
            <h4>SiMBA</h4>
            <p>Kemahasiswaan</p>
        </div>
    </div>

    <ul class="menu">
        <li class="menu-item">
            <a href="{{ route('kemahasiswaan_beranda') }}">
                <i class="fas fa-home"></i> Beranda
            </a>
        </li>
        <!-- Konseling dengan submenu -->
        <li class="menu-item">
            <a href="javascript:void(0);" onclick="toggleSubMenu('konseling-submenu')">
                <i class="fas fa-file-alt"></i> Konseling
                <i class="fas fa-chevron-down submenu-toggle" id="konseling-toggle"></i>
            </a>
        </li>
        <ul class="submenu" id="konseling-submenu" style="display: none;">
            <li class="submenu-item">
                <a href="{{ route('kemahasiswaan_daftar_pelanggaran') }}">
                    <i class="fas fa-list"></i> Daftar Pelanggaran
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('kemahasiswaan_hasil_konseling') }}">
                    <i class="fas fa-book"></i> Hasil Konseling
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('kemahasiswaan_riwayat_konseling') }}">
                    <i class="fas fa-history"></i> Riwayat Konseling
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('kemahasiswaan_konseling_lanjutan') }}">
                    <i class="fas fa-forward"></i> Konseling Lanjutan
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('kemahasiswaan_ajukan_konseling') }}">
                    <i class="fas fa-user-friends"></i> Ajukan Konseling

                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('kemahasiswaan_daftar_request') }}">
                    <i class="fas fa-book-open"></i> Daftar Request
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('kemahasiswaan_riwayat_daftar_request') }}">
                    <i class="fas fa-book-open"></i> Riwayat Daftar Request
                </a>
            </li>
        </ul>
    </ul>
</div>