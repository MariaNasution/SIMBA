@extends('layouts.app')

@section('content')

<strong class="dosen_wali">
    Anak wali dari {{ $dosenNotifications->first()->nama ?? 'Unknown Dosen' }}
</strong>

<div class="status-box">
    @if($perwalian)
        <p><strong>Hari:</strong> {{ \Carbon\Carbon::parse($perwalian->Tanggal)->translatedFormat('l, d F Y') }}</p>
        
        @if ($perwalian->Status == 'Scheduled')
        <p><strong>Status:</strong> <span class="status-{{ strtolower($perwalian->Status) }}">{{ $perwalian->Status }}</span></p>

        @else 
        <p><strong>Status:</strong> <span class="status-{{ strtolower($absensi->status_kehadiran) }}">{{ $absensi->status_kehadiran }}</span></p>
        <p><strong>Keterangan:</strong> <span>{{ $absensi->keterangan }}</span></p>

        @endif

    @else
        <p><strong>Hari:</strong> Tidak tersedia</p>
        <p><strong>Status:</strong> <span class="status-unknown">Tidak diketahui</span></p>
    @endif
</div>

@endsection

@section('styles')
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

    .status-scheduled {
        background-color: #007bff; /* Blue for "Scheduled" */
        color: white; /* White text */
        padding: 2px 8px; /* Padding for the status label */
        border-radius: 10px; /* Rounded corners for the status */
    }

    .status-izin {
        background-color: #ffc107; /* Yellow for "Izin" */
        color: black; /* Black text for readability */
        padding: 2px 8px; /* Padding for the status label */
        border-radius: 10px; /* Rounded corners for the status */
    }

    .status-hadir {
        background-color: #28a745; /* Green for "Hadir" */
        color: white; /* White text */
        padding: 2px 8px; /* Padding for the status label */
        border-radius: 10px; /* Rounded corners for the status */
    }

    .status-alpa {
        background-color: #dc3545; /* Red for "Alpa" */
        color: white; /* White text */
        padding: 2px 8px; /* Padding for the status label */
        border-radius: 10px; /* Rounded corners for the status */
    }

    .status-unknown {
        background-color: #6c757d; /* Gray for "Unknown" */
        color: white; /* White text */
        padding: 2px 8px; /* Padding for the status label */
        border-radius: 10px; /* Rounded corners for the status */
    }
</style>
@endsection