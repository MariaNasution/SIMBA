@extends('layouts.app')

@section('content')
    <div class="main-content flex-grow-1 p-4">
        <!-- Feedback Messages -->
        <div id="feedbackMessage" class="alert alert-dismissible fade show d-none" role="alert">
            <span id="feedbackText"></span>
            <button type="button" class="btn-close" onclick="hideFeedback()"></button>
        </div>

        <!-- Notifications Section -->
        @if ($notifications->isNotEmpty())
            <div class="notifications mb-4">
                <h5 style="font-size: 18px; font-weight: 600; color: #333;">Notifications</h5>
                <ul class="list-group">
                    @foreach ($notifications as $notification)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $notification->data['message'] }}</span>
                            @if (!$notification->read_at)
                                <button class="btn btn-sm btn-primary" onclick="markAsRead('{{ $notification->id }}')">Mark as Read</button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Class Selection -->
        <div class="mb-4">
            <label for="classSelect" style="font-size: 16px; font-weight: 500; color: #333;">Select Class for Perwalian:</label>
            <select id="classSelect" name="selectedClass" class="form-select" style="max-width: 200px;" onchange="updateSelectedClass()">
                @if (!$defaultClass)
                    <option value="" disabled selected>Select a class</option>
                @endif
                @forelse ($classes as $class)
                    <option value="{{ $class }}" {{ $defaultClass === $class ? 'selected' : '' }}>{{ $class }}</option>
                @empty
                    <option value="" disabled>No classes available</option>
                @endforelse
            </select>
        </div>

        <!-- Calendar Section -->
        <div class="calendar-container mb-5 mx-auto position-relative" style="max-width: 500px; background: #fff;">
            <div class="d-flex justify-content-between align-items-center mb-2 p-3 border-bottom">
                <button onclick="changeMonth('{{ $currentDate->copy()->subMonth()->format('Y-m') }}')" class="btn btn-outline-secondary" style="border-radius: 4px;" id="prevMonthBtn"><</button>
                <h4 class="text-center flex-grow-1 mb-0" style="font-weight: 600; color: #333;" id="monthLabel">
                    {{ $currentDate->format('F Y') }}
                </h4>
                <button onclick="changeMonth('{{ $currentDate->copy()->addMonth()->format('Y-m') }}')" class="btn btn-outline-secondary" style="border-radius: 4px;" id="nextMonthBtn">></button>
            </div>
            <div class="card-body p-0" id="calendarBody">
                @include('perwalian.partials.calendar', ['currentDate' => $currentDate, 'calendarData' => $calendarData])
            </div>
        </div>

        <!-- Form Section -->
        <div class="d-flex justify-content-end mt-4">
            <form id="requestForm" onsubmit="handleFormSubmit(event)">
                @csrf
                <input type="hidden" id="methodField" name="_method" value="">
                <label for="selectedDate" class="visually-hidden">Selected Date for Perwalian:</label>
                <input type="hidden" id="selectedDate" name="selectedDate" value="">
                <label for="selectedClass" class="visually-hidden">Selected Class for Perwalian:</label>
                <input type="hidden" id="selectedClass" name="selectedClass" value="">
                <button type="submit" class="btn px-4 py-2" id="actionButton" 
                    style="background-color: #28A745; color: white; border: none; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transition: all 0.3s ease;"
                    disabled>
                    <span id="buttonText">Request</span>
                    <span id="buttonLoading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>
        </div>
        <p class="text-muted mt-2" style="font-size: 12px;" id="noteText"></p>

        <!-- Related Dosen Section -->
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

    <!-- Styles -->
    <style>
        .border-bottom-line { border-bottom: 1px solid #E5E5E5; padding-bottom: 10px; }
        .calendar-container { width: 100%; border: none; }
        .calendar-table { width: 100%; border-collapse: collapse; background: #fff; border: none; font-weight: bold; }
        .calendar-table thead th { border: none; text-align: center; padding: 12px; color: #13946D; font-size: 14px; font-weight: bold; transition: background-color 0.2s; }
        .calendar-table th, .calendar-table td { border: none; text-align: center; padding: 12px; font-size: 18px; transition: background-color 0.2s; }
        .calendar-table th { color: #666; text-transform: uppercase; font-weight: 500; }
        .day { position: relative; }
        .day.clickable { cursor: pointer; }
        .day.clickable:hover { background-color: #f0f0f0; }
        .day:not(.clickable) { cursor: not-allowed; opacity: 0.6; }
        .day.past { background-color: #f8f8f8; color: #ccc; cursor: not-allowed; opacity: 0.5; }
        .today { background-color: #e9ecef; font-weight: 600; }
        .weekend .date-number { color: #DC3545; }
        .dot { width: 6px; height: 6px; border-radius: 50%; display: block; margin: 2px auto; }
        .holiday-dot { background-color: #DC3545; }
        .scheduled-dot { background-color: #28A745; }
        .date-number { display: block; margin-bottom: 2px; }
        .btn:hover { box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); }
        .selected-date { position: relative; }
        .selected-date::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 30px; height: 30px; border: 2px solid #28A745; border-radius: 50%; z-index: 1; }
        #feedbackMessage.alert-success { background-color: #d4edda; color: #155724; }
        #feedbackMessage.alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>

    <!-- Scripts -->
    <script>
        let scheduledClasses = @json($scheduledClasses);
        let scheduledDatesByClass = @json($scheduledDatesByClass);
        const storeRoute = "{{ route('set.perwalian.store') }}";
        const destroyRoute = "{{ route('set.perwalian.destroy') }}";
        const calendarRoute = "{{ route('set.perwalian.calendar') }}";
        const csrfToken = "{{ csrf_token() }}";

        let currentlySelectedDay = null;
        let currentMonth = "{{ $currentDate->format('Y-m') }}";
        let prevMonth = "{{ $currentDate->copy()->subMonth()->format('Y-m') }}";
        let nextMonth = "{{ $currentDate->copy()->addMonth()->format('Y-m') }}";

        function showFeedback(message, type = 'success') {
            const feedbackMessage = document.getElementById('feedbackMessage');
            const feedbackText = document.getElementById('feedbackText');
            feedbackText.textContent = message;
            feedbackMessage.classList.remove('d-none', 'alert-success', 'alert-danger');
            feedbackMessage.classList.add(`alert-${type}`);
            setTimeout(hideFeedback, 5000);
        }

        function hideFeedback() {
            const feedbackMessage = document.getElementById('feedbackMessage');
            feedbackMessage.classList.add('d-none');
        }

        function updateScheduledMarkers() {
            const classSelect = document.getElementById('classSelect');
            const selectedClass = classSelect.value;
            const scheduledDates = scheduledDatesByClass[selectedClass] || [];

            document.querySelectorAll('.scheduled-dot').forEach(dot => {
                dot.style.display = 'none';
            });

            document.querySelectorAll('.day').forEach(day => {
                const date = day.getAttribute('data-date');
                if (date && scheduledDates.includes(date)) {
                    day.querySelector('.scheduled-dot').style.display = 'block';
                }
            });
        }

        function updateSelectedClass() {
            const classSelect = document.getElementById('classSelect');
            const selectedClassInput = document.getElementById('selectedClass');
            const actionButton = document.getElementById('actionButton');
            const buttonText = document.getElementById('buttonText');
            const requestForm = document.getElementById('requestForm');
            const methodField = document.getElementById('methodField');
            const noteText = document.getElementById('noteText');

            selectedClassInput.value = classSelect.value;
            const isScheduled = scheduledClasses.includes(classSelect.value);

            if (isScheduled) {
                actionButton.style.backgroundColor = '#DC3545';
                buttonText.textContent = 'Edit';
                requestForm.action = destroyRoute;
                methodField.value = 'DELETE';
                noteText.style.display = 'none';
                actionButton.disabled = !classSelect.value;
            } else {
                actionButton.style.backgroundColor = '#28A745';
                buttonText.textContent = 'Request';
                requestForm.action = storeRoute;
                methodField.value = '';
                noteText.style.display = 'block';
                const selectedDate = document.getElementById('selectedDate').value;
                actionButton.disabled = !(classSelect.value && selectedDate);
            }

            updateScheduledMarkers();
            const days = document.querySelectorAll('.day');
            if (classSelect.value && !isScheduled) {
                days.forEach(day => {
                    if (!day.classList.contains('past')) {
                        day.classList.add('clickable');
                    }
                });
                showFeedback('Class selected: ' + classSelect.value, 'success');
            } else {
                days.forEach(day => {
                    if (!isScheduled) {
                        day.classList.remove('clickable');
                    }
                    if (day === currentlySelectedDay && !isScheduled) {
                        day.classList.remove('selected-date');
                        currentlySelectedDay = null;
                        document.getElementById('selectedDate').value = '';
                    }
                });
                if (!classSelect.value) {
                    showFeedback('Please select a class to interact with the calendar.', 'danger');
                }
            }
        }

        function selectDate(date, element) {
            const classSelect = document.getElementById('classSelect');
            const isScheduled = scheduledClasses.includes(classSelect.value);
            if (!classSelect.value || isScheduled) return;

            const selectedDateInput = document.getElementById('selectedDate');
            const selectedClassInput = document.getElementById('selectedClass');
            const actionButton = document.getElementById('actionButton');

            selectedDateInput.value = date;

            if (currentlySelectedDay) {
                currentlySelectedDay.classList.remove('selected-date');
            }

            element.classList.add('selected-date');
            currentlySelectedDay = element;

            if (selectedClassInput.value) {
                actionButton.disabled = false;
            }

            showFeedback('Date selected: ' + date, 'success');
        }

        function changeMonth(month) {
            const calendarBody = document.getElementById('calendarBody');
            calendarBody.innerHTML = '<p>Loading...</p>';
            document.getElementById('prevMonthBtn').disabled = true;
            document.getElementById('nextMonthBtn').disabled = true;

            fetch(`${calendarRoute}?month=${month}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                calendarBody.innerHTML = data.calendarHtml;
                document.getElementById('monthLabel').textContent = data.monthLabel;
                prevMonth = data.prevMonth;
                nextMonth = data.nextMonth;
                document.getElementById('prevMonthBtn').setAttribute('onclick', `changeMonth('${prevMonth}')`);
                document.getElementById('nextMonthBtn').setAttribute('onclick', `changeMonth('${nextMonth}')`);
                document.getElementById('prevMonthBtn').disabled = prevMonth === '2024-12';
                document.getElementById('nextMonthBtn').disabled = nextMonth === '2028-01';
                currentMonth = month;
                updateSelectedClass();

                const selectedDateInput = document.getElementById('selectedDate');
                const selectedDate = selectedDateInput.value;
                if (selectedDate && !selectedDate.startsWith(month)) {
                    selectedDateInput.value = '';
                    currentlySelectedDay = null;
                    document.getElementById('actionButton').disabled = true;
                    showFeedback('Selected date cleared as it is not in the current month.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error fetching calendar:', error);
                calendarBody.innerHTML = '<p>Error loading calendar.</p>';
                showFeedback('Failed to load the calendar. Please try again.', 'danger');
            });
        }

        function handleFormSubmit(event) {
            event.preventDefault();
            const form = document.getElementById('requestForm');
            const actionButton = document.getElementById('actionButton');
            const buttonText = document.getElementById('buttonText');
            const buttonLoading = document.getElementById('buttonLoading');
            const method = form.querySelector('#methodField').value || 'POST';
            const url = method === 'DELETE' ? destroyRoute : storeRoute;

            actionButton.disabled = true;
            buttonText.classList.add('d-none');
            buttonLoading.classList.remove('d-none');

            const data = {
                selectedDate: document.getElementById('selectedDate').value,
                selectedClass: document.getElementById('selectedClass').value,
                _method: method === 'DELETE' ? 'DELETE' : undefined,
            };

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(data),
            })
            .then(response => response.text().then(text => ({ status: response.status, text })))
            .then(({ status, text }) => {
                let json;
                try {
                    json = JSON.parse(text);
                } catch (e) {
                    throw new Error(`Failed to parse response as JSON: ${e.message}. Response: ${text}`);
                }

                actionButton.disabled = false;
                buttonText.classList.remove('d-none');
                buttonLoading.classList.add('d-none');

                if (status === 200 && json.success) {
                    scheduledClasses = json.scheduledClasses;
                    scheduledDatesByClass = json.scheduledDatesByClass;
                    updateSelectedClass();
                    document.getElementById('selectedDate').value = '';
                    if (currentlySelectedDay) {
                        currentlySelectedDay.classList.remove('selected-date');
                        currentlySelectedDay = null;
                    }
                    showFeedback(json.message, 'success');
                } else {
                    showFeedback(json.message || 'Unknown error occurred', 'danger');
                }
            })
            .catch(error => {
                console.error('Error submitting form:', error);
                actionButton.disabled = false;
                buttonText.classList.remove('d-none');
                buttonLoading.classList.add('d-none');
                showFeedback('Failed to process the request: ' + error.message, 'danger');
            });
        }

        function markAsRead(notificationId) {
            fetch('/notifications/' + notificationId + '/read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI: Remove the "Mark as Read" button
                    const button = document.querySelector(`button[onclick="markAsRead('${notificationId}')"]`);
                    if (button) button.remove();
                    showFeedback('Notification marked as read', 'success');
                } else {
                    showFeedback('Failed to mark notification as read', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFeedback('Error marking notification as read', 'danger');
            });
        }

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = "{{ route('logout') }}";
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedClass();
            document.getElementById('prevMonthBtn').disabled = prevMonth === '2024-12';
            document.getElementById('nextMonthBtn').disabled = nextMonth === '2028-01';
        });
    </script>
@endsection