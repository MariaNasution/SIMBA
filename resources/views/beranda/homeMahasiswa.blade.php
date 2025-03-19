@extends('layouts.app') <!-- Assuming this is your main layout -->

@section('content')
<!-- Header with Notification and Logout -->
<div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
        <a href="{{ route('beranda') }}">Home</a>
    </h3>

    <!-- Notification Dropdown -->
    <div class="dropdown position-relative me-3">
        <a href="#" class="text-decoration-none" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell fs-5 cursor-pointer" title="Notifications"></i>
            @if ($notifications->count() > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $notifications->count() }}
                <span class="visually-hidden">unread notifications</span>
            </span>
            @endif
        </a>
        <ul class="dropdown-menu" aria-labelledby="notificationDropdown">
            <li>
                <h6 class="dropdown-header">Notifikasi</h6>
            </li>
            @forelse ($notifications as $notif)
            <li>
                <a class="dropdown-item" href="#">{{ $notif->Pesan }}</a>
            </li>
            @empty
            <li><a class="dropdown-item" href="#">Tidak ada notifikasi</a></li>
            @endforelse
        </ul>
    </div>

    <!-- Logout Button -->
    <a href="#" onclick="confirmLogout()">
        <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
    </a>
</div>

<!-- Main Content -->
<div class="app-content-header">
    <div class="container-fluid">
        <!-- Current Date and Time -->
        <div class="d-flex justify-content-start align-items-center">
            <p class="text-muted mb-3">{{ now()->addHours(7)->isoFormat('dddd, D MMMM YYYY HH:mm') }}</p>
        </div>

        <!-- Study Progress and Announcements -->
        <div class="row">
            <!-- Study Progress Chart -->
            <div class="col-md-6">
                <div class="card p-3 shadow-sm">
                    <h5 class="card-title">Kemajuan Studi</h5>
                    <canvas id="kemajuanStudiChart" height="400"></canvas>
                </div>
            </div>

            <!-- Announcements -->
            <div class="col-md-6">
                <div class="cards p-3">
                    <h5 class="border-bottom-line text-start">PENGUMUMAN</h5>
                    <ul class="list-unstyled text-start pengumuman">
                        @forelse ($pengumuman as $item)
                        <li>
                            <a href="{{ route('pengumuman.detail', $item->id) }}" class="text-decoration-none">
                                <strong
                                    class="@switch($item->sumber)
                                            @case('BEM') text-primary @break
                                            @case('INFO') text-danger @break
                                            @case('BURSAR') text-info @break
                                            @case('KEASRAMAAN') text-success @break
                                            @case('KEMAHASISWAAN') text-purple @break
                                            @default text-dark
                                        @endswitch">
                                    [{{ strtoupper($item->sumber) }}]
                                </strong>
                                {{ $item->judul }}
                            </a>
                        </li>
                        @empty
                        <li class="text-muted">Belum ada pengumuman.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Calendar Download Buttons -->
        <div class="row mt-4">
            <div class="col-md-12 text-center">
                @if ($akademik)
                <a href="{{ asset('storage/' . $akademik->file_path) }}" target="_blank"
                    class="btn btn-primary me-2">
                    Unduh Kalender Akademik!
                </a>
                @else
                <button class="btn btn-secondary me-2" disabled>Kalender Akademik Belum Tersedia</button>
                @endif

                @if ($bem)
                <a href="{{ asset('storage/' . $bem->file_path) }}" target="_blank" class="btn btn-primary">
                    Unduh Kalender NonAkademik!
                </a>
                @else
                <button class="btn btn-secondary" disabled>Kalender BEM Belum Tersedia</button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Announcement Modal (Unchanged) -->
<div class="modal fade" id="pengumumanModal" tabindex="-1" aria-labelledby="pengumumanModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pengumumanModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="pengumumanDeskripsi"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection