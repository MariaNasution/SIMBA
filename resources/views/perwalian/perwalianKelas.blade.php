@extends('layouts.app')

@section('content')
    <style>
        /* General styling for layout */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #fff;
        }

        /* Breadcrumb and header styling */
        .breadcrumb {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .breadcrumb .back-btn {
            display: inline-flex;
            align-items: center;
            background-color: #4a90e2; /* Blue background from Figma */
            color: #fff;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 8px; /* Rounded corners */
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .breadcrumb .back-btn:hover {
            background-color: #357abd; /* Darker blue on hover */
        }

        .breadcrumb .back-btn .arrow {
            margin-right: 5px;
            font-size: 16px;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }

        h2 {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Button styling */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        }

        .btn-success {
            background-color: #28a745;
            color: #fff;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-success:hover, .btn-secondary:hover {
            opacity: 0.9;
        }

        /* Align buttons to the right */
        .button-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: 700;
            color: #333;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* Status button styling */
        .status-btn {
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            margin-right: 5px;
            border-radius: 5px;
            font-size: 12px;
            width: 40px; /* Fixed width for consistency */
            text-align: center;
        }

        .present { background-color: #4CAF50; color: white; }
        .absent { background-color: #f44336; color: white; }
        .permission { background-color: #ff9800; color: white; }

        /* Status display styling */
        .status-display {
            display: none;
            font-weight: 500;
        }

        .selected-status {
            margin-right: 5px;
        }

        /* Dropdown styling */
        .dropdown {
            display: none;
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            min-width: 120px;
        }

        .dropdown div {
            padding: 8px;
            cursor: pointer;
        }

        .dropdown div:hover {
            background: #f1f1f1;
        }

        /* Editable Keterangan */
        .keterangan-input {
            display: none;
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .keterangan-input:focus {
            outline: none;
            border-color: #007bff;
        }
    </style>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ url()->previous() }}" class="back-btn">
            <span class="arrow">⟵</span> Back
        </a>
    </div>

    <!-- Header -->
    <h1>Absensi Mahasiswa / IF1 Angkatan 2022</h1>
    <h2>Quick Access</h2>

    <!-- Quick Access and Simpan Buttons -->
    <div class="button-container">
        <button onclick="markAllHadir()" class="btn btn-success">✅ Quick Access</button>
    </div>

    <!-- Attendance Table -->
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Status Kehadiran</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
            <tr>
                <td>{{ $student['nim'] ?? 'N/A' }}</td>
                <td>{{ $student['nama'] ?? 'Unknown' }}</td>
                <td class="status-cell" onclick="showDropdown(this)">
                    <div class="status-buttons">
                        <button class="status-btn present" onclick="updateStatus(event, this, '✅', 'Hadir')">✅</button>
                        <button class="status-btn absent" onclick="updateStatus(event, this, '❌', 'Alpha')">❌</button>
                        <button class="status-btn permission" onclick="updateStatus(event, this, '📝', 'Izin')">📝</button>
                    </div>
                    <div class="status-display">
                        <span class="selected-status"></span>
                    </div>
                    <div class="dropdown">
                        <div onclick="updateStatus(event, this, '✅', 'Hadir')">✅ Hadir</div>
                        <div onclick="updateStatus(event, this, '❌', 'Alpha')">❌ Alpha</div>
                        <div onclick="updateStatus(event, this, '📝', 'Izin')">📝 Izin</div>
                    </div>
                </td>
                <td class="status-desc">
                    <span class="keterangan-text">{{ $student['keterangan'] ?? '' }}</span>
                    <input type="text" class="keterangan-input" placeholder="Enter keterangan">
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No students found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <br>
    <br>
    <div class="button-container">
        
        <button class="btn btn-secondary bg-primary">Simpan</button>
    </div>


    <script>
        function updateStatus(event, element, emoji, text) {
            event.stopPropagation(); // Prevents event bubbling

            let cell = element.closest('.status-cell');
            let statusDisplay = cell.querySelector('.status-display');
            let statusButtons = cell.querySelector('.status-buttons');
            let dropdown = cell.querySelector('.dropdown');
            let statusDesc = cell.closest('tr').querySelector('.status-desc');
            let keteranganText = statusDesc.querySelector('.keterangan-text');
            let keteranganInput = statusDesc.querySelector('.keterangan-input');

            // Update the status display
            statusDisplay.innerHTML = `<span class="selected-status">${emoji} ${text}</span>`;
            statusButtons.style.display = "none";
            statusDisplay.style.display = "block";
            dropdown.style.display = "none";

            // Show keterangan input if "Izin" is selected
            if (text === 'Izin') {
                keteranganText.style.display = 'none';
                keteranganInput.style.display = 'inline-block';
                keteranganInput.focus();
            } else {
                keteranganText.style.display = 'inline';
                keteranganInput.style.display = 'none';
                keteranganText.textContent = text === 'Hadir' ? '' : text;
            }
        }

        function showDropdown(cell) {
            let dropdown = cell.querySelector('.dropdown');

            if (dropdown.style.display === "none" || dropdown.style.display === "") {
                dropdown.style.display = "block";
            } else {
                dropdown.style.display = "none";
            }
        }

        // Hide dropdowns when clicking outside
        document.addEventListener("click", function(event) {
            let dropdowns = document.querySelectorAll(".dropdown");
            dropdowns.forEach(function(dropdown) {
                if (!dropdown.parentElement.contains(event.target)) {
                    dropdown.style.display = "none";
                }
            });
        });

        function goBack() {
            window.history.back(); // Goes to the previous page
        }

        function markAllHadir() {
            document.querySelectorAll(".status-cell").forEach(statusCell => {
                let statusDisplay = statusCell.querySelector('.status-display');
                let statusButtons = statusCell.querySelector('.status-buttons');
                let dropdown = statusCell.querySelector('.dropdown');
                let statusDesc = statusCell.closest('tr').querySelector('.status-desc');
                let keteranganText = statusDesc.querySelector('.keterangan-text');
                let keteranganInput = statusDesc.querySelector('.keterangan-input');

                statusDisplay.innerHTML = `<span class="selected-status">✅ Hadir</span>`;
                statusButtons.style.display = "none";
                statusDisplay.style.display = "block";
                dropdown.style.display = "none";

                keteranganText.style.display = 'inline';
                keteranganInput.style.display = 'none';
                keteranganText.textContent = '';
            });
        }
    </script>
@endsection