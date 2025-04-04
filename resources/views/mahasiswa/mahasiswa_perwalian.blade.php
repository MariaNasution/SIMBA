@extends('layouts.app')

@section('content')


<strong class="dosen_wali">
    Anak wali dari {{ $dosenNotifications->first()->nama ?? 'Unknown Dosen' }}
</strong>

<div class="status-box">
    @if($perwalian)
        <p><strong>Hari:</strong> {{ \Carbon\Carbon::parse($perwalian->Tanggal)->translatedFormat('l, d F Y') }}</p>
        <p><strong>Status:</strong> <span class="status-{{ strtolower($perwalian->Status) }}">{{ $perwalian->Status }}</span></p>
    @else
        <p><strong>Hari:</strong> Tidak tersedia</p>
        <p><strong>Status:</strong> <span class="status-unknown">Tidak diketahui</span></p>
    @endif
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

    .status-scheduled {
        background-color: #28a745; /* Green background for "Hadir" */
        color: white; /* White text */
        padding: 2px 8px; /* Padding for the status label */
        border-radius: 10px; /* Rounded corners for the status */
    }
</style>

@endsection