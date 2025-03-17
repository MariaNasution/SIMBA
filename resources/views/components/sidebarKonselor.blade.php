<div class="sidebar bg-dark text-light vh-100">
    <div class="sidebar-header">
        <img src="{{ asset('assets\img\Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
        <div class="header-text">
            <h4>SiMBA</h4>
            <p>Konselor</p>
        </div>
    </div>

    <ul class="menu">
        <li class="menu-item">
            <a href="{{ route('konselor') }}">
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
                <a href="{{ route('daftar_pelanggaran') }}">
                    <i class="fas fa-list"></i> Daftar Pelanggaran
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('hasil_konseling') }}">
                    <i class="fas fa-book"></i> Hasil Konseling
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('riwayat_konseling') }}">
                    <i class="fas fa-history"></i> Riwayat Konseling
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('konseling_lanjutan') }}">
                    <i class="fas fa-forward"></i> Konseling Lanjutan
                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('ajukan_konseling') }}">
                    <i class="fas fa-user-friends"></i> Ajukan Konseling

                </a>
            </li>
            <li class="submenu-item">
                <a href="{{ route('daftar_request') }}">
                    <i class="fas fa-book-open"></i> Daftar Request
                </a>
            </li>
        </ul>
    </ul>
</div>