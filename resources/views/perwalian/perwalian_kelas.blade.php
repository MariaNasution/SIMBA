@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4" style="color:rgb(10, 11, 14);">Perwalian/Perwalian Kelas</h1>
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
                    @for ($i = 1; $i <= 10; $i++)
                        <tr>
                            <td>{{ $i }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
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
            color: #1a73e8; /* Change the text color to blue */
        }

        .custom-table td {
            background-color: #ffffff;
        }

        .custom-table tr {
            height: 40px; /* Adjust row height to match the image */
        }

        /* Ensure the table takes up the full width */
        .table-responsive {
            overflow-x: auto;
        }

        /* Style for the header */
        h1 {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
@endsection