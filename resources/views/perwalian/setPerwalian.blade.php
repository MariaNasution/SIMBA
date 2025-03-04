@extends('layouts.app')

@section('content')

    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
        <a> Set Perwalian</a> 
        </h3>
        <a href="#" onclick="confirmLogout()">
        <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>
    <div class="container-fluid py-4">

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

        <!-- Calendar -->
        <div id="calendar" class="card shadow-sm mb-4 mx-auto" style="max-width: 800px; border-radius: 8px;">
            <div class="card-body p-3">
                <!-- FullCalendar will render here -->
            </div>
        </div>

        <!-- Form for Setting Perwalian Date -->
        <div class="text-center mt-4">
            <form method="POST" action="{{ route('set.perwalian.store') }}">
                @csrf <!-- Laravel CSRF token for security -->
                <label for="selectedDate" class="visually-hidden">Selected Date for Perwalian:</label>
                <input type="hidden" id="selectedDate" name="selectedDate" value="">
                <button type="submit" class="btn btn-success px-4 py-2">Request</button>
            </form>
        </div>
    </div>

    <!-- FullCalendar JS (using CDN for simplicity; move to assets for production) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl.querySelector('.card-body'), {
                initialView: 'dayGridMonth',
                selectable: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'monthSelector'
                },
                customButtons: {
                    monthSelector: {
                        text: 'Month',
                        click: function() {
                            var monthSelect = document.createElement('select');
                            const months = [
                                'January', 'February', 'March', 'April', 'May', 'June'
                            ];
                            months.forEach((month, index) => {
                                var option = document.createElement('option');
                                option.value = index;
                                option.text = month + ' 2025';
                                monthSelect.appendChild(option);
                            });
                            monthSelect.value = 1; // Set initial month to February 2025 (index 1 for February)
                            monthSelect.onchange = function() {
                                calendar.gotoDate(new Date(2025, this.value, 1));
                            };
                            monthSelect.classList.add('form-select', 'form-select-sm', 'border', 'rounded', 'shadow-sm'); // Bootstrap + custom styling
                            monthSelect.style.maxWidth = '150px'; // Limit width for better fit
                            calendarEl.querySelector('.fc-header-toolbar').appendChild(monthSelect);
                        }
                    }
                },
                dateClick: function(info) {
                    calendar.unselect();
                    var selectedDate = info.dateStr;
                    $('#selectedDate').val(selectedDate);
                    // alert('You selected: ' + selectedDate);
                },
                validRange: {
                    start: '2025-01-01',
                    end: '2025-06-30' // Limit to January–June 2025
                },
                height: 'auto', // Adjust height to fit content
                dayMaxEvents: true, // Prevent overlapping events
                eventTimeFormat: { // Optional: Customize time format if needed
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false
                }
            });
            calendar.render();
        });
    </script>

    <style>
        /* Ensure these styles override others with higher specificity */
        #calendar .fc {
            border: none !important;
        }

        #calendar .fc-header-toolbar {
            margin-bottom: 15px;
            text-align: center;
        }

        #calendar .fc-button {
            background-color: #fff;
            border: 1px solid #ddd;
            color: #333;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            box-shadow: none !important;
        }

        #calendar .fc-button:hover {
            background-color: #f1f1f1;
            border-color: #ddd;
        }

        #calendar .fc-button-active {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        #calendar .fc-daygrid-day-number {
            color: #333 !important; /* Black text for dates, matching screenshot */
        }

        #calendar .fc-daygrid-day:hover {
            background-color: #f8f9fa !important; /* Light gray hover effect */
        }

        #calendar .fc-today {
            background-color: #e9ecef !important; /* Light gray for today, matching screenshot */
        }

        #calendar .fc-title {
            font-size: 18px;
            font-weight: bold;
            color: #333 !important; /* Black text for title, matching screenshot */
        }

        /* Ensure the month dropdown matches the design */
        #calendar .form-select {
            background-color: #fff;
            border-color: #ddd;
            color: #333;
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 4px;
            box-shadow: none !important;
        }

        #calendar .form-select:focus {
            border-color: #ddd;
            box-shadow: none;
        }
    </style>
@endsection