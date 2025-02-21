@extends('layouts.app')

@section('content')
    <style>
        /* General styling for layout and table */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        /* Breadcrumb and header styling */
        .breadcrumb {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
            font-weight: bold;
        }

        h2 {
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }

        /* Button styling */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: quick-access-button;
            font-size: 14px;
            margin-right: 10px;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
        }

        .btn-success {
            background-color: #28a745;
            color: #fff;
        }

        .btn-primary:hover, .btn-success:hover {
            opacity: 0.9;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
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
        }

        .present { background-color: #4CAF50; color: white; }
        .absent { background-color: #f44336; color: white; }
        .permission { background-color: #ff9800; color: white; }

        /* Dropdown styling */
        .dropdown {
            display: none;
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .dropdown div {
            padding: 8px;
            cursor: pointer;
        }

        .dropdown div:hover {
            background: #f1f1f1;
        }
    </style>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ url()->previous() }}">← Back</a>
    </div>

    <!-- Header -->
    <h1>Absensi Mahasiswa / IF 1 Angkatan 2022</h1>
    <h2>Quick Access</h2>

    <!-- Quick Access Buttons -->
    <button onclick="goBack()" class="btn btn-primary">Back</button>
    <button onclick="markAllHadir()" class="btn btn-success">✅ Quick Access</button>

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
            @foreach(['Yanrikho Sicilagan', 'Joel Bonar Septian Sinambela', 'Rajphael Zefanya Siahaan', 'Pangeran Simamora', 'Olga Frischilla G.', 'Febiola Cindy Tampubolon', 'Patricia Agustin Sibarani', 'DHEA GRACE A. SIMANJUNTAK', 'William Napitupulu', 'Christian Theofani Napitpulu', 'Jonathan Martinus Pangaribuan', 'Baha Ambrosius Sibarani', 'Gabriela Amelia Silitonga'] as $name)
            <tr>
                <td></td> <!-- You can dynamically add NIM here if available -->
                <td>{{ $name }}</td>
                <td class="status-cell" onclick="showDropdown(this)">
                    <div class="status-buttons">
                        <button class="status-btn present" onclick="updateStatus(event, this, '✅', 'Hadir')">✅</button>
                        <button class="status-btn absent" onclick="updateStatus(event, this, '❌', 'Alpha')">❌</button>
                        <button class="status-btn permission" onclick="updateStatus(event, this, '📝', 'Izin')">📝</button>
                    </div>
                    <div class="status-display" style="display: none;">
                        <span class="selected-status"></span>
                    </div>
                    <div class="dropdown">
                        <div onclick="updateStatus(event, this, '✅', 'Hadir')">✅ Hadir</div>
                        <div onclick="updateStatus(event, this, '❌', 'Alpha')">❌ Alpha</div>
                        <div onclick="updateStatus(event, this, '📝', 'Izin')">📝 Izin</div>
                    </div>
                </td>
                <td class="status-desc"></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        function updateStatus(event, element, emoji, text) {
            event.stopPropagation(); // Prevents event bubbling

            let cell = element.closest('.status-cell');
            let statusDisplay = cell.querySelector('.status-display');
            let statusButtons = cell.querySelector('.status-buttons');
            let dropdown = cell.querySelector('.dropdown');
            let statusDesc = cell.closest('tr').querySelector('.status-desc');

            // Update the status display
            statusDisplay.innerHTML = `<span class="selected-status">${emoji} ${text}</span>`;
            statusDesc.textContent = text; // Update the description column with the status text

            // Show the status display and dropdown, hide buttons
            statusButtons.style.display = "none";
            statusDisplay.style.display = "block";
            dropdown.style.display = "none";
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
            document.querySelectorAll("td:nth-child(3)").forEach(statusCell => {
                statusCell.innerHTML = "✅ Hadir"; // Change the status
            });

            document.querySelectorAll("td:nth-child(4)").forEach(keteranganCell => {
                keteranganCell.textContent = "Hadir"; // Update the description column
            });
        }
    </script>
@endsection