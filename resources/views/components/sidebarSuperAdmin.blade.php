<div class="sidebar">
  <div class="sidebar-header">
    <img src="{{ asset('assets\img\Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
    <div class="header-text">
      <h4>SiMBA</h4>
      <p>Admin</p>
    </div>
  </div>
  
  <ul class="menu">
    <li class="menu-item">
      <a href="{{ route('admin_beranda') }}">
        <i class="fas fa-home"></i> Beranda
      </a>
    </li>

    <li class="menu-item">
      <a href="{{ route('admin_add-user') }}">
        <i class="fas fa-user-friends"></i> Tambahkan pengguna
      </a>
    </li>
  </ul>
</div>