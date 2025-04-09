@extends('layouts.app')

@section('content')
    <style>
        table {
            width: 100%;
            border-collapse: collapse; /* Ensure borders collapse properly */
            margin-top: 20px;
            border: 2px solid #aaa; /* Add border to the table itself */
        }

        th, td {
            border: 2px solid #aaa; /* Consistent border for all cells */
            padding: 8px;
            text-align: left;
            box-sizing: border-box; /* Ensure padding doesn’t increase cell size */
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold; /* Ensure headers stand out */
        }

        /* Ensure the empty state cell also has proper borders */
        td[colspan="4"] {
            text-align: center;
            border: 2px solid #aaa; /* Ensure the colspan cell has borders */
        }

        .btn {
            background-color: #4CAF50;
            color: #fff;
            margin-top: 20px;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 20px;
        }

        .status-cell {
            position: relative;
            cursor: pointer;
            display: flex; /* Keep flex for centering */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            padding: 8px; /* Match td padding to avoid size differences */
            box-sizing: border-box; /* Ensure padding doesn’t increase cell size */
        }

        .status-buttons {
            display: flex;
            justify-content: center; /* Center the buttons */
            gap: 5px;
        }

        .status-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .present {
            opacity: 100%;
            color: white;
        }

        .absent {
            opacity: 100%;
            color: white;
        }

        .permission {
            opacity: 100%;
            color: black;
        }

        .dropdown {
            display: none;
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .dropdown div {
            padding: 8px;
            cursor: pointer;
        }

        .dropdown div:hover {
            background-color: #f0f0f0;
        }

        .status-display {
            display: none;
        }

        .status-desc {
            position: relative;
        }

        .keterangan-input {
            display: none;
            width: 100%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
            box-sizing: border-box; /* Ensure input fits within cell */
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            background-color: #68B8EA;
            color: #fff;
            font-size: 18px;
            font-weight: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 30px;
            margin-left: 10px;
        }

        .back-btn:hover {
            background-color: #4A9CD6;
        }

        .back-btn .arrow {
            font-size: 40px;
            margin-right: 0px;
            line-height: 1;
        }

        form button {
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            box-shadow: none;
            outline: none;
            text-align: center;
        }

        form button:hover {
            opacity: 0.8;
            cursor: pointer;
        }

        .status-btn {
            text-align: center;
        }
    </style>

    <div class="mb-3" style="text-align: left;">
        <button onclick="goBack()" class="btn back-btn">
            <span class="arrow">&lt;</span>Back
        </button>
    </div>

    <!-- Header -->
    <h1>{{ $title }}</h1>
    <h2>Quick Access</h2>

    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Form for Attendance -->
    <form id="attendanceForm" action="{{ route('absensi.store', ['date' => $date, 'class' => $class]) }}" method="POST" onsubmit="return confirm('Are you sure you want to save the attendance data?');">
        @csrf

        <div class="button-container">
            <button type="button" onclick="markAllHadir()" class="btn">Quick Access</button>
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
                            <input type="hidden" name="attendance[{{ $student['nim'] }}][status]" class="attendance-status" value="{{ $student['status_kehadiran'] ?? '' }}">
                            <div class="status-buttons" style="{{ $student['status_kehadiran'] ? 'display: none;' : '' }}">
                                <button type="button" class="status-btn present" onclick="updateStatus(event, this, '✅', 'Hadir')">✅</button>
                                <button type="button" class="status-btn absent" onclick="updateStatus(event, this, '❌', 'Alpa')">❌</button>
                                <button type="button" class="status-btn permission" onclick="updateStatus(event, this, '📝', 'Izin')">📝</button>
                            </div>
                            <div class="status-display" style="{{ $student['status_kehadiran'] ? 'display: block;' : 'display: none;' }}">
                                <span class="selected-status">
                                    @if ($student['status_kehadiran'] === 'hadir')
                                        ✅ Hadir
                                    @elseif ($student['status_kehadiran'] === 'alpa')
                                        ❌ Alpa
                                    @elseif ($student['status_kehadiran'] === 'izin')
                                        📝 Izin
                                    @endif
                                </span>
                            </div>
                            <div class="dropdown">
                                <div onclick="updateStatus(event, this, '✅', 'Hadir')">✅ Hadir</div>
                                <div onclick="updateStatus(event, this, '❌', 'Alpa')">❌ Alpa</div>
                                <div onclick="updateStatus(event, this, '📝', 'Izin')">📝 Izin</div>
                            </div>
                        </td>
                        <td class="status-desc">
                            <input type="hidden" name="attendance[{{ $student['nim'] }}][keterangan]" class="attendance-keterangan" value="{{ $student['keterangan'] ?? '' }}">
                            <span class="keterangan-text" style="{{ $student['status_kehadiran'] === 'izin' ? 'display: none;' : '' }}">{{ $student['keterangan'] ?? '' }}</span>
                            <input type="text" class="keterangan-input" placeholder="Enter keterangan" value="{{ $student['keterangan'] ?? '' }}" style="{{ $student['status_kehadiran'] === 'izin' ? 'display: inline-block;' : 'display: none;' }}">
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No students found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Save Button -->
        <div class="button-container">
            <button type="submit" class="btn">Simpan</button>
        </div>
    </form>

    <script>
        function updateStatus(event, element, emoji, text) {
            event.stopPropagation();
            event.preventDefault();

            let cell = element.closest('.status-cell');
            let statusDisplay = cell.querySelector('.status-display');
            let statusButtons = cell.querySelector('.status-buttons');
            let dropdown = cell.querySelector('.dropdown');
            let statusDesc = cell.closest('tr').querySelector('.status-desc');
            let keteranganText = statusDesc.querySelector('.keterangan-text');
            let keteranganInput = statusDesc.querySelector('.keterangan-input');
            let attendanceStatusInput = cell.querySelector('.attendance-status');
            let attendanceKeteranganInput = statusDesc.querySelector('.attendance-keterangan');

            statusDisplay.innerHTML = `<span class="selected-status">${emoji} ${text}</span>`;
            statusButtons.style.display = "none";
            statusDisplay.style.display = "block";
            dropdown.style.display = "none";
            attendanceStatusInput.value = text;

            if (text === 'Izin') {
                keteranganText.style.display = 'none';
                keteranganInput.style.display = 'inline-block';
                keteranganInput.focus();
                attendanceKeteranganInput.value = keteranganInput.value;
            } else {
                keteranganText.style.display = 'inline';
                keteranganInput.style.display = 'none';
                keteranganText.textContent = text === 'Hadir' ? '' : keteranganInput.value;
                attendanceKeteranganInput.value = text === 'Hadir' ? '' : keteranganInput.value;
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

        document.addEventListener("click", function(event) {
            let dropdowns = document.querySelectorAll(".dropdown");
            dropdowns.forEach(function(dropdown) {
                if (!dropdown.parentElement.contains(event.target)) {
                    dropdown.style.display = "none";
                }
            });
        });

        function markAllHadir() {
            document.querySelectorAll(".status-cell").forEach(statusCell => {
                let statusDisplay = statusCell.querySelector('.status-display');
                let statusButtons = statusCell.querySelector('.status-buttons');
                let dropdown = statusCell.querySelector('.dropdown');
                let statusDesc = statusCell.closest('tr').querySelector('.status-desc');
                let keteranganText = statusDesc.querySelector('.keterangan-text');
                let keteranganInput = statusDesc.querySelector('.keterangan-input');
                let attendanceStatusInput = statusCell.querySelector('.attendance-status');
                let attendanceKeteranganInput = statusDesc.querySelector('.attendance-keterangan');

                statusDisplay.innerHTML = `<span class="selected-status">✅ Hadir</span>`;
                statusButtons.style.display = "none";
                statusDisplay.style.display = "block";
                dropdown.style.display = "none";
                attendanceStatusInput.value = 'Hadir';
                attendanceKeteranganInput.value = '';

                keteranganText.style.display = 'inline';
                keteranganInput.style.display = 'none';
                keteranganText.textContent = '';
            });
        }

        document.querySelectorAll('.keterangan-input').forEach(input => {
            input.addEventListener('change', function() {
                let statusDesc = this.closest('.status-desc');
                let attendanceKeteranganInput = statusDesc.querySelector('.attendance-keterangan');
                attendanceKeteranganInput.value = this.value;
            });
        });

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                }
            });
        });

        function goBack() {
            window.history.back();
        }
    </script>
@endsection