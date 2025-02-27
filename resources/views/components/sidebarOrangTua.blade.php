<div class="sidebar">
  <div class="sidebar-header">
    <img src="{{ asset('assets\img\Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
    <div class="header-text">
      <h4>SIMBA</h4>
      <p>Orang Tua</p>
    </div>
  </div>
  <div class="profile-section">
    <div class="profile-card">
      <a href="{{ route('profil') }}">
        <img src="{{ asset('assets/img/profil.jpg') }}" alt="Profile Picture" class="profile-picture">
        <h4 class="profile-name">
          {{ session('student_data.nama') ?? 'Nama Tidak Ditemukan' }}
        </h4>
        <p class="profile-id">
          {{ session('student_data.nim') ?? 'NIM Tidak Ditemukan' }}
        </p>
      </a>
    </div>
  </div>
  <ul class="menu">
    <li class="menu-item">
      <a href="{{ route('orang_tua') }}">
        <i class="fas fa-home"></i> Beranda
      </a>
    </li>
    <li class="menu-item">
      <a href="{{ route('bursar') }}">
        <i class="fas fa-wallet"></i> Bursar
      </a>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" onclick="toggleSubMenu('perkuliahan-submenu')">
        <i class="fas fa-book"></i> Perkuliahan
        <i class="fas fa-chevron-left submenu-toggle" id="perkuliahan-toggle"></i>
      </a>
      <ul class="submenu" id="perkuliahan-submenu" style="display: none;">
        <li class="submenu-item">
          <a href="{{ route('prodi') }}">
            <i class="fas fa-user"></i> Prodi
          </a>
        </li>
        <li class="submenu-item">
          <a href="{{ route('jadwal') }}">
            <i class="fas fa-calendar-alt"></i> Jadwal
          </a>
        </li>
        <li class="submenu-item">
          <a href="{{ route('absensi') }}">
            <i class="fas fa-address-card"></i> Absensi Mahasiswa
          </a>
        </li>
        <li class="submenu-item">
          <a href="{{ route('kemajuan_studi') }}">
            <i class="fas fa-chart-line"></i> Kemajuan Studi
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="javascript:void(0);" onclick="toggleSubMenu('perizinan-submenu')">
        <i class="fas fa-file-alt"></i> Perizinan
        <i class="fas fa-chevron-left submenu-toggle" id="perizinan-toggle"></i>
      </a>
      <ul class="submenu" id="perizinan-submenu" style="display: none;">
        <li class="submenu-item">
          <a href="{{ route('izin_bermalam') }}">
            <i class="fas fa-moon"></i> Izin Bermalam
          </a>
        </li>
        <li class="submenu-item">
          <a href="{{ route('izin_keluar') }}">
            <i class="fas fa-walking"></i> Izin Keluar
          </a>
        </li>
      </ul>
    </li>
    <li class="menu-item">
      <a href="{{ route('asrama') }}">
        <i class="fas fa-building"></i> Asrama
      </a>
    </li>
    <li class="menu-item">
      <a href="{{ route('catatan_perilaku_orang_tua') }}">
        <i class="fas fa-user-edit"></i> Catatan Perilaku
      </a>
    </li>
  </ul>
</div>