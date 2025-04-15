@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>Nama Dosen Wali</th>
                        <th>Kelas</th>
                        <th>Angkatan</th>
                        <th>Waktu Perwalian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($perwalianList as $index => $perwalian)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                {{ isset($dosenList[$perwalian->ID_Dosen_Wali]) ? $dosenList[$perwalian->ID_Dosen_Wali]->nama : 'N/A' }}
                            </td>
                            <td>{{ $perwalian->kelas ?: 'N/A' }}</td>
                            <td>
                                {{ isset($dosenWaliList[$perwalian->ID_Dosen_Wali]) ? $perwalian['angkatan'] : 'N/A' }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($perwalian->Tanggal)->format('d M Y H:i') }} - 
                                {{ \Carbon\Carbon::parse($perwalian->Tanggal_Selesai)->format('H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No scheduled Perwalian records found for mahasiswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .custom-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        .custom-table th,
        .custom-table td {
            border: 1px solid #d3d3d3;
            padding: 8px;
            text-align: left;
        }

        .custom-table th {
            background-color: #f5f6fa;
            font-weight: bold;
            color: #1a73e8; /* Blue text for headers */
        }

        .custom-table td {
            background-color: #ffffff;
        }

        /* Alternating row colors */
        .custom-table tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        /* Hover effect */
        .custom-table tr:hover td {
            background-color: #e8f0fe;
        }

        .custom-table tr {
            height: 40px; /* Adjust row height */
        }

        /* Ensure the table takes up the full width */
        .table-responsive {
            overflow-x: auto;
        }

        /* Style for the header */
        h1 {
            font-size: 24px;
            font-weight: bold;
            color: rgb(10, 11, 14); /* Moved from inline style */
        }

        /* Center text for empty message */
        .text-center {
            text-align: center;
        }
    </style>
@endsection