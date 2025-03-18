@extends('layouts.app')

@section('content')

<div class="d-flex align-items-center mb-4 border-bottom-line">
    <h3 class="me-auto">
        <a href="{{ route('beranda') }}">Home</a>
        <a> / Perwalian </a>
    </h3>
    <!-- Notification Dropdown -->
    <div class="dropdown position-relative me-3">
        <a href="#" class="text-decoration-none" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-bell fs-5 cursor-pointer" title="Notifications"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                {{ $notificationCount }} <!-- Dynamic notification count -->
                <span class="visually-hidden">unread notifications</span>
            </span>
        </a>
        <ul class="dropdown-menu" aria-labelledby="notificationDropdown">
            <li><h6 class="dropdown-header">Notifications</h6></li>
            <!-- Dynamic Perwalian Notifications -->
            @forelse ($notifications as $notification)
                <li>
                    <a class="dropdown-item" href="#">
                        {{ $notification->Pesan }} by Dosen Nama: {{ $notification->perwalian->nama ?? 'Unknown' }}
                    </a>
                </li>
            @empty
                <li><a class="dropdown-item text-muted">No notifications available.</a></li>
            @endforelse
        </ul>
    </div>
    <!-- Logout Button -->
    <a href="#" onclick="confirmLogout()">
        <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
    </a>
</div>

<strong class="dosen_wali"> Anak wali dari Herimanto S.kom </strong>

<!-- New box for date and status -->
<div class="status-box">
    <p><strong>Hari:</strong> Sabtu, 15 Februari 2025</p>
    <p><strong>Status:</strong> <span class="status-hadir">Hadir</span></p>
</div>

<style>
    .dosen_wali {
        text-align: left;
        display: block;
    }

    /* Styling for the new status box */
    .status-box {
        text-align: left;
        background-color: #e9ecef; /* Light gray background like in the image */
        padding: 25px; /* Add some padding for spacing */
        border: 1px solid #ced4da; /* Light border */
        border-radius: 5px; /* Slightly rounded corners */
        width: 700px; /* Adjust width to content */
        height: 280px;
        margin-top: 20px; /* Space above the box */
    }

    .status-box p {
        margin: 0; /* Remove default paragraph margins */
        line-height: 1.5; /* Adjust line spacing */
        margin-bottom: 10px;
    }

    .status-hadir {
        background-color: #28a745; /* Green background for "Hadir" */
        color: white; /* White text */
        padding: 2px 8px; /* Padding for the status label */
        border-radius: 10px; /* Rounded corners for the status */
    }
</style>

@endsection