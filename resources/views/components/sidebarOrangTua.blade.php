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
      <a href="{{ route('orang_tua') }}">
        <img src="{{ asset('assets/img/profil.jpg') }}" alt="Profile Picture" class="profile-picture">
        <h4 class="profile-name">
          {{ session('student_data_ortu.nama') ?? 'Nama Tidak Ditemukan' }}
        </h4>
        <p class="profile-id">
          {{ session('student_data_ortu.nim') ?? 'NIM Tidak Ditemukan' }}
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
      <a href="{{ route('catatan_perilaku_orang_tua') }}">
        <i class="fas fa-user-edit"></i> Catatan Perilaku
      </a>
    </li>
  </ul>
</div>