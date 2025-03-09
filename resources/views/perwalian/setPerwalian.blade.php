@extends('layouts.app')

@section('content')
    <div class="main-content flex-grow-1 p-4">
        <!-- Header -->
        <div class="d-flex align-items-center mb-4 border-bottom-line">
            <h3 class="me-auto" style="font-size: 24px; font-weight: 700; color: #333;">Set Perwalian</h3>
            <a href="#" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5 cursor-pointer" style="color: #333; font-size: 24px;" title="Logout"></i>
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Calendar Container -->
        <div class="calendar-container mb-4 mx-auto" style="max-width: 400px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <button class="btn btn-outline-secondary" onclick="changeMonth(-1)"><</button>
                <h4 class="text-center flex-grow-1 mb-0" id="calendar-title"></h4>
                <button class="btn btn-outline-secondary" onclick="changeMonth(1)">></button>
            </div>
            <div class="card shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-0">
                    <div id="calendar-month"></div>
                </div>
            </div>
            @if (session('perwalian_requested'))
                <p class="text-center mt-2" style="color: #28A745;">Perwalian telah di-request</p>
            @endif
        </div>

        <!-- Request/Edit Button -->
        <div class="text-center mt-4">
            <form method="POST" action="{{ route('set.perwalian.store') }}" id="requestForm">
                @csrf
                <label for="selectedDate" class="visually-hidden">Selected Date for Perwalian:</label>
                <input type="hidden" id="selectedDate" name="selectedDate" value="">
                <button type="submit" class="btn btn-success px-4 py-2" id="actionButton" style="background-color: #28A745; color: white; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                    @if (session('perwalian_requested'))
                        Edit
                    @else
                        Request
                    @endif
                </button>
            </form>
        </div>
    </div>

    <!-- JavaScript for Calendar -->
    <script>
        let currentDate = new Date(2025, 0); // Start with January 2025

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            const months = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            document.getElementById('calendar-title').textContent = `${months[month]} ${year}`;

            // Marked dates with red dots
            const markedDates = {
                '2025-01-13': true, '2025-01-14': true, '2025-01-15': true, '2025-01-16': true,
                '2025-01-17': true, '2025-01-18': true, '2025-01-21': true, '2025-01-24': true,
                '2025-01-25': true, '2025-02-09': true, '2025-02-10': true, '2025-02-11': true,
                '2025-02-13': true
            };

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDay = firstDay.getDay();

            let html = '<table class="calendar-table"><thead><tr>';
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            days.forEach(day => html += `<th>${day}</th>`);
            html += '</tr></thead><tbody>';

            let date = 1;
            for (let i = 0; i < 6; i++) {
                html += '<tr>';
                for (let j = 0; j < 7; j++) {
                    if (i === 0 && j < startingDay) {
                        html += '<td></td>';
                    } else if (date > daysInMonth) {
                        html += '<td></td>';
                    } else {
                        const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
                        const isMarked = markedDates[currentDateStr] ? '<div class="dot"></div>' : '';
                        html += `<td class="day ${new Date().toISOString().split('T')[0] === currentDateStr ? 'today' : ''}" onclick="selectDate('${currentDateStr}')">${date}${isMarked}</td>`;
                        date++;
                    }
                }
                html += '</tr>';
                if (date > daysInMonth) break;
            }
            html += '</tbody></table>';
            document.getElementById('calendar-month').innerHTML = html;
        }

        function changeMonth(offset) {
            currentDate.setMonth(currentDate.getMonth() + offset);
            if (currentDate < new Date(2025, 0)) currentDate = new Date(2025, 0); // Limit to January 2025
            if (currentDate > new Date(2025, 5)) currentDate = new Date(2025, 5); // Limit to June 2025
            renderCalendar();
        }

        function selectDate(dateStr) {
            document.getElementById('selectedDate').value = dateStr;
            console.log('Selected date:', dateStr);
        }

        // Initial render
        renderCalendar();
    </script>

    <style>
        .border-bottom-line {
            border-bottom: 1px solid #E5E5E5;
            padding-bottom: 10px;
        }

        .calendar-container {
            width: 100%;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }

        .calendar-table th,
        .calendar-table td {
            border: 1px solid #ddd;
            text-align: center;
            padding: 8px;
            font-size: 14px;
        }

        .calendar-table th {
            background-color: #f8f9fa;
            color: #666;
            text-transform: uppercase;
            font-weight: 500;
        }

        .day {
            cursor: pointer;
            position: relative;
        }

        .day:hover {
            background-color: #f8f9fa;
        }

        .today {
            background-color: #e9ecef;
        }

        .dot {
            width: 6px;
            height: 6px;
            background-color: #DC3545; /* Red dot as per Figma */
            border-radius: 50%;
            display: block;
            margin: 2px auto;
        }
    </style>
@endsection