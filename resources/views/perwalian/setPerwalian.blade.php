@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto" style="font-size: 24px; font-weight: 700; color: #333;">Set Perwalian</h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" style="color: #333; font-size: 24px;" title="Logout"></i>
        </a>
    </div>
    <div class="main-content flex-grow-1 p-4">
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

        <!-- Class Selection Dropdown -->
        <div class="mb-4">
            <label for="classSelect" style="font-size: 16px; font-weight: 500; color: #333;">Select Class for Perwalian:</label>
            <select id="classSelect" name="selectedClass" class="form-select" style="max-width: 200px;" onchange="updateSelectedClass()">
                <option value="" disabled selected>Select a class</option>
                @forelse ($classes as $class)
                    <option value="{{ $class }}">{{ $class }}</option>
                @empty
                    <option value="" disabled>No classes available</option>
                @endforelse
            </select>
        </div>

        <!-- Calendar Container -->
        <div class="calendar-container mb-5 mx-auto position-relative" style="max-width: 500px; background: #fff;">
            <div class="d-flex justify-content-between align-items-center mb-2 p-3 border-bottom">
                <form method="GET" action="{{ route('set.perwalian') }}" class="d-inline">
                    <input type="hidden" name="month" value="{{ $currentDate->copy()->subMonth()->format('Y-m') }}">
                    <button type="submit" class="btn btn-outline-secondary" style="border-radius: 4px;"><</button>
                </form>
                <h4 class="text-center flex-grow-1 mb-0" style="font-weight: 600; color: #333;">
                    {{ $currentDate->format('F Y') }}
                </h4>
                <form method="GET" action="{{ route('set.perwalian') }}" class="d-inline">
                    <input type="hidden" name="month" value="{{ $currentDate->copy()->addMonth()->format('Y-m') }}">
                    <button type="submit" class="btn btn-outline-secondary" style="border-radius: 4px;">></button>
                </form>
            </div>
            <div class="card-body p-0">
                <!-- Static HTML Table Structure -->
                @php
                    $firstDay = $currentDate->copy()->startOfMonth();
                    $lastDay = $currentDate->copy()->endOfMonth();
                    $daysInMonth = $lastDay->day;
                    $startingDay = $firstDay->dayOfWeek;
                    $markedDates = [
                        '2025-01-13' => true, '2025-01-14' => true, '2025-01-15' => true, '2025-01-16' => true,
                        '2025-01-17' => true, '2025-01-18' => true, '2025-01-21' => true, '2025-01-24' => true,
                        '2025-01-25' => true, '2025-02-09' => true, '2025-02-10' => true, '2025-02-11' => true,
                        '2025-02-13' => true,
                    ];
                    $calendarDays = array_fill(0, 42, '');
                    $date = 1;
                    for ($i = $startingDay; $i < $startingDay + $daysInMonth; $i++) {
                        $calendarDays[$i] = $date;
                        $date++;
                    }
                @endphp

                <table class="calendar-table">
                    <thead>
                        <tr>
                            <th>Sun</th>
                            <th>Mon</th>
                            <th>Tue</th>
                            <th>Wed</th>
                            <th>Thu</th>
                            <th>Fri</th>
                            <th>Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @for($i = 0; $i < 7; $i++)
                                @php
                                    $currentDateStr = $calendarDays[$i] ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d') : '';
                                    $isToday = $currentDateStr === now()->format('Y-m-d');
                                    $isMarked = $currentDateStr && isset($markedDates[$currentDateStr]);
                                    $isWeekend = ($i % 7 === 0 || $i % 7 === 6); // Sunday (0) or Saturday (6)
                                @endphp
                                <td class="day {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                                    @if($calendarDays[$i])
                                        onclick="document.getElementById('selectedDate').value = '{{ $currentDateStr }}'; document.getElementById('actionButton').disabled = false;"
                                    @endif>
                                    <span class="date-number">{{ $calendarDays[$i] ?: '' }}</span>
                                    @if($isMarked)
                                        <div class="dot"></div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            @for($i = 7; $i < 14; $i++)
                                @php
                                    $currentDateStr = $calendarDays[$i] ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d') : '';
                                    $isToday = $currentDateStr === now()->format('Y-m-d');
                                    $isMarked = $currentDateStr && isset($markedDates[$currentDateStr]);
                                    $isWeekend = ($i % 7 === 0 || $i % 7 === 6);
                                @endphp
                                <td class="day {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                                    @if($calendarDays[$i])
                                        onclick="document.getElementById('selectedDate').value = '{{ $currentDateStr }}'; document.getElementById('actionButton').disabled = false;"
                                    @endif>
                                    <span class="date-number">{{ $calendarDays[$i] ?: '' }}</span>
                                    @if($isMarked)
                                        <div class="dot"></div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            @for($i = 14; $i < 21; $i++)
                                @php
                                    $currentDateStr = $calendarDays[$i] ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d') : '';
                                    $isToday = $currentDateStr === now()->format('Y-m-d');
                                    $isMarked = $currentDateStr && isset($markedDates[$currentDateStr]);
                                    $isWeekend = ($i % 7 === 0 || $i % 7 === 6);
                                @endphp
                                <td class="day {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                                    @if($calendarDays[$i])
                                        onclick="document.getElementById('selectedDate').value = '{{ $currentDateStr }}'; document.getElementById('actionButton').disabled = false;"
                                    @endif>
                                    <span class="date-number">{{ $calendarDays[$i] ?: '' }}</span>
                                    @if($isMarked)
                                        <div class="dot"></div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            @for($i = 21; $i < 28; $i++)
                                @php
                                    $currentDateStr = $calendarDays[$i] ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d') : '';
                                    $isToday = $currentDateStr === now()->format('Y-m-d');
                                    $isMarked = $currentDateStr && isset($markedDates[$currentDateStr]);
                                    $isWeekend = ($i % 7 === 0 || $i % 7 === 6);
                                @endphp
                                <td class="day {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                                    @if($calendarDays[$i])
                                        onclick="document.getElementById('selectedDate').value = '{{ $currentDateStr }}'; document.getElementById('actionButton').disabled = false;"
                                    @endif>
                                    <span class="date-number">{{ $calendarDays[$i] ?: '' }}</span>
                                    @if($isMarked)
                                        <div class="dot"></div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            @for($i = 28; $i < 35; $i++)
                                @php
                                    $currentDateStr = $calendarDays[$i] ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d') : '';
                                    $isToday = $currentDateStr === now()->format('Y-m-d');
                                    $isMarked = $currentDateStr && isset($markedDates[$currentDateStr]);
                                    $isWeekend = ($i % 7 === 0 || $i % 7 === 6);
                                @endphp
                                <td class="day {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                                    @if($calendarDays[$i])
                                        onclick="document.getElementById('selectedDate').value = '{{ $currentDateStr }}'; document.getElementById('actionButton').disabled = false;"
                                    @endif>
                                    <span class="date-number">{{ $calendarDays[$i] ?: '' }}</span>
                                    @if($isMarked)
                                        <div class="dot"></div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                        <tr>
                            @for($i = 35; $i < 42; $i++)
                                @php
                                    $currentDateStr = $calendarDays[$i] ? $currentDate->copy()->setDay($calendarDays[$i])->format('Y-m-d') : '';
                                    $isToday = $currentDateStr === now()->format('Y-m-d');
                                    $isMarked = $currentDateStr && isset($markedDates[$currentDateStr]);
                                    $isWeekend = ($i % 7 === 0 || $i % 7 === 6);
                                @endphp
                                <td class="day {{ $isToday ? 'today' : '' }} {{ $isWeekend ? 'weekend' : '' }}"
                                    @if($calendarDays[$i])
                                        onclick="document.getElementById('selectedDate').value = '{{ $currentDateStr }}'; document.getElementById('actionButton').disabled = false;"
                                    @endif>
                                    <span class="date-number">{{ $calendarDays[$i] ?: '' }}</span>
                                    @if($isMarked)
                                        <div class="dot"></div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
            @if ($perwalian_requested)
                <p class="text-center mt-2 position-absolute start-50 translate-middle-x" style="color: #28A745; font-weight: 500; font-size: 14px; background-color: #e6f4ea; width: fit-content; padding: 4px 12px; border-radius: 12px;">
                    Perwalian telah di-request
                </p>
            @endif
        </div>

        <!-- Request/Edit Button -->
        <div class="d-flex justify-content-end mt-4">
            <form method="POST" action="{{ $perwalian_requested ? route('set.perwalian.destroy') : route('set.perwalian.store') }}" id="requestForm">
                @csrf
                @if ($perwalian_requested)
                    @method('DELETE')
                @endif
                <label for="selectedDate" class="visually-hidden">Selected Date for Perwalian:</label>
                <input type="hidden" id="selectedDate" name="selectedDate" value="">
                <label for="selectedClass" class="visually-hidden">Selected Class for Perwalian:</label>
                <input type="hidden" id="selectedClass" name="selectedClass" value="">
                <button type="submit" class="btn px-4 py-2" id="actionButton" 
                    style="background-color: {{ $perwalian_requested ? '#DC3545' : '#28A745' }}; color: white; border: none; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;"
                    @if(!$perwalian_requested) disabled @endif>
                    @if ($perwalian_requested)
                        <span>Edit</span>
                    @else
                        <span>Request</span>
                    @endif
                </button>
            </form>
        </div>
        @if(!$perwalian_requested)
            <p class="text-muted mt-2" style="font-size: 12px;">Note: You can only request once per class. Use 'Edit' to delete and request again.</p>
        @endif

        <!-- Dosen Notifications (Optional Display) -->
        @if(!empty($dosenNotifications) && $dosenNotifications->count() > 0)
            <div class="mt-4">
                <h5 style="font-size: 18px; font-weight: 600; color: #333;">Related Dosen</h5>
                <ul class="list-group">
                    @foreach($dosenNotifications as $dosen)
                        <li class="list-group-item">
                            {{ $dosen['nama'] ?? 'Unknown Dosen' }} (NIP: {{ $dosen['nip'] }})
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <style>
        .border-bottom-line {
            border-bottom: 1px solid #E5E5E5;
            padding-bottom: 10px;
        }

        .calendar-container {
            width: 100%;
            border: none;
        }

        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: none;
            font-weight: bold;
        }

        .calendar-table thead th {
            border: none;
            text-align: center;
            padding: 12px;
            color: #13946D;
            font-size: 14px;
            font-weight: bold;
            transition: background-color 0.2s;
        }

        .calendar-table th,
        .calendar-table td {
            border: none;
            text-align: center;
            padding: 12px;
            font-size: 18px;
            transition: background-color 0.2s;
        }

        .calendar-table th {
            color: #666;
            text-transform: uppercase;
            font-weight: 500;
        }

        .day {
            cursor: pointer;
            position: relative;
        }

        .day:hover {
            background-color: #f0f0f0;
        }

        .today {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .weekend .date-number {
            color: #DC3545; /* Red color for Sundays and Saturdays */
        }

        .dot {
            width: 6px;
            height: 6px;
            background-color: #DC3545;
            border-radius: 50%;
            display: block;
            margin: 2px auto;
        }

        .date-number {
            display: block;
            margin-bottom: 2px;
        }

        .btn:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .debug-status {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
    </style>

    <script>
        function updateSelectedClass() {
            const classSelect = document.getElementById('classSelect');
            const selectedClassInput = document.getElementById('selectedClass');
            selectedClassInput.value = classSelect.value;
            // Enable the button if both a class and a date are selected
            if (classSelect.value && document.getElementById('selectedDate').value) {
                document.getElementById('actionButton').disabled = false;
            } else {
                document.getElementById('actionButton').disabled = true;
            }
        }

        // Update the onclick event for calendar days to also check for class selection
        document.querySelectorAll('.day').forEach(day => {
            day.addEventListener('click', function() {
                const selectedDateInput = document.getElementById('selectedDate');
                const selectedClassInput = document.getElementById('selectedClass');
                if (this.querySelector('.date-number').textContent) {
                    selectedDateInput.value = this.getAttribute('onclick').match(/'(\d{4}-\d{2}-\d{2})'/)[1];
                    if (selectedClassInput.value) {
                        document.getElementById('actionButton').disabled = false;
                    }
                }
            });
        });

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = "{{ route('logout') }}"; // Adjust route as needed
            }
        }
    </script>
@endsection