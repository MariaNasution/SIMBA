<div class="sidebar">
  <div class="sidebar-header">
    <img src="{{ asset('assets\img\Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
    <div class="header-text">
      <h4>SiMBA</h4>
      <p>Mahasiswa</p>
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
      <a href="{{ route('beranda') }}">
        <i class="fas fa-home"></i> Beranda
      </a>
    </li>
    <li class="menu-item">
      <a href="{{ route('mahasiswa_konseling') }}">
        <i class="fas fa-user-friends"></i> Konseling
      </a>
    </li>
    <li class="menu-item">
      <a href="{{ route('mahasiswa_perwalian') }}">
        <i class="fas fa-list-alt"></i> Perwalian
      </a>
    </li>
    <li class="menu-item">
      <a href="{{ route('catatan_perilaku') }}">
          <i class="fas fa-user-edit"></i> Catatan perilaku
      </a>
  </li>
  </ul>
</div>