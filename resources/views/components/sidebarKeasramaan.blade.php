<div class="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('assets\img\Logo Institut Teknologi Del.png') }}" alt="Logo" class="header-logo">
        <div class="header-text">
            <h4>SIMBA</h4>
            <p>Keasramaan</p>
        </div>
    </div>
    <ul class="menu">
        <li class="menu-item">
            <a href="{{ route('keasramaan') }}">
                <i class="fas fa-home"></i> Beranda
            </a>
        </li>
        <li class="menu-item">
            <a href="{{ route('pelanggaran_keasramaan') }}">
                <i class="fas fa-user-edit"></i> Catatan perilaku
            </a>
        </li>
    </ul>
</div>