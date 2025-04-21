<div class="sidebar">
  <div class="sidebar-header">
    <img src="{{ asset('assets/img/Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
    <div class="header-text">
      <h4>SiMBA</h4>
      <p>Admin</p>
    </div>
  </div>

  <ul class="menu">
    <li class="menu-item">
      <a href="{{ route('admin.beranda') }}">
        <i class="fas fa-home"></i> Beranda
      </a>
    </li>

    <li class="menu-item">
      <a href="{{ route('admin.users.index') }}">
        <i class="fas fa-users-cog"></i> Kelola Pengguna
      </a>
    </li>

    <li class="menu-item">
      <a href="{{ route('admin.users.create') }}">
        <i class="fas fa-user-plus"></i> Tambah Pengguna
      </a>
    </li>

  </ul>
</div>
