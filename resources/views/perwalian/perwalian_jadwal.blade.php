@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    
        <!-- Success Popup -->
        <div id="successPopup" class="alert alert-success" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1000;">
            Successfully scheduled Perwalian!
        </div>

        <!-- Form Jadwal -->
        <div class="row mt-4 mx-2">
            <div class="col-md-8">
                <h5>Jadwalkan Perwalian</h5>

                <form id="perwalianForm">
                    @csrf

                    <div class="row">
                        <!-- Jadwal Mulai -->
                        <div class="col-md-6 mb-3">
                            <label for="jadwalMulai" class="form-label">Jadwal Mulai</label>
                            <div class="input-group">
                                <input type="datetime-local" class="form-control custom-input" id="jadwalMulai"
                                    name="jadwalMulai" required>
                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                            </div>
                            <span class="text-danger" id="jadwalMulaiError"></span>
                        </div>

                        <!-- Jadwal Selesai -->
                        <div class="col-md-6 mb-3">
                            <label for="jadwalSelesai" class="form-label">Jadwal Selesai</label>
                            <div class="input-group">
                                <input type="datetime-local" class="form-control custom-input" id="jadwalSelesai"
                                    name="jadwalSelesai" required>
                                <span class="input-group-text"><i class="far fa-clock"></i></span>
                            </div>
                            <span class="text-danger" id="jadwalSelesaiError"></span>
                        </div>
                    </div>

                    <!-- Dropdown Keterangan -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <select class="form-select custom-select" id="keterangan" name="keterangan" required>
                                <option selected disabled>Pilih Keterangan</option>
                                <option value="Semester Baru">Semester Baru</option>
                                <option value="Sebelum UTS">Sebelum UTS</option>
                                <option value="Sebelum UAS">Sebelum UAS</option>
                            </select>
                            <span class="text-danger" id="keteranganError"></span>
                        </div>
                    </div>

                    <!-- Tombol Jadwalkan -->
                    <div class="row mt-2">
                        <div class="col-md-3">
                            <button type="submit" class="btn custom-btn w-100">Jadwalkan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('perwalianForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // Clear previous error messages
            document.getElementById('jadwalMulaiError').textContent = '';
            document.getElementById('jadwalSelesaiError').textContent = '';
            document.getElementById('keteranganError').textContent = '';

            const formData = new FormData(this);

            fetch("{{ route('kemahasiswaan_perwalian.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success popup and reset form
                    const popup = document.getElementById('successPopup');
                    popup.style.display = 'block';
                    setTimeout(() => {
                        popup.style.display = 'none';
                    }, 3000); // Fade away after 3 seconds
                    this.reset(); // Clear form inputs
                } else {
                    // Display user-friendly error messages
                    document.getElementById('jadwalMulaiError').textContent = data.errors?.jadwalMulai?.[0] 
                        ? data.errors.jadwalMulai[0].replace('Jadwal Mulai', 'Start Date') 
                        : '';
                    document.getElementById('jadwalSelesaiError').textContent = data.errors?.jadwalSelesai?.[0] 
                        ? data.errors.jadwalSelesai[0].replace('jadwalSelesai', 'End Date').replace('The End Date must be a date after jadwalMulai.', 'End Date must be after Start Date.') 
                        : '';
                    document.getElementById('keteranganError').textContent = data.errors?.keterangan?.[0] 
                        ? formatKeteranganError(data.errors.keterangan[0]) 
                        : data.message || 'An error occurred while scheduling. Please check your inputs.';

                    // Handle general error messages
                    if (data.message && !data.errors) {
                        document.getElementById('keteranganError').textContent = formatGeneralError(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('keteranganError').textContent = 'An unexpected error occurred. Please try again.';
            });
        });

        // Function to format keterangan error messages
        function formatKeteranganError(error) {
            // Check if the error is about an invalid timeframe
            if (error.includes('not valid for the date')) {
                // Extract date, expected keterangan, and selected keterangan
                const dateMatch = error.match(/date (\d{4}-\d{2}-\d{2})/);
                const expectedMatch = error.match(/Expected timeframe: (.*?)$/);
                const selectedKeterangan = error.match(/keterangan '(.*?)'/);
                
                const date = dateMatch ? dateMatch[1] : 'the selected date';
                const expected = expectedMatch ? expectedMatch[1] : 'the appropriate period';
                const selected = selectedKeterangan ? selectedKeterangan[1] : 'the selected type';

                // Provide guidance based on expected keterangan
                let guidance = '';
                switch (expected) {
                    case 'Semester Baru':
                        guidance = 'This can only be scheduled in January, August, or late May.';
                        break;
                    case 'Sebelum UTS':
                        guidance = 'This can only be scheduled in February, early March, or September to mid-October.';
                        break;
                    case 'Sebelum UAS':
                        guidance = 'This can only be scheduled in mid-March to early May, mid-October to early December.';
                        break;
                    default:
                        guidance = 'Please select the correct period for this date.';
                }

                return `${selected} is not valid for ${date}. Please select ${expected}. ${guidance}`;
            }
            // Handle other keterangan errors (e.g., required field)
            return error.replace('keterangan', 'Perwalian Type').replace('The Perwalian Type field is required.', 'Please select a Perwalian Type.');
        }

        // Function to format general error messages
        function formatGeneralError(message) {
            if (message.includes('already scheduled on this date')) {
                return 'A Perwalian session is already scheduled for this date. Please choose a different date.';
            }
            if (message.includes('No dosen wali usernames found')) {
                return 'No academic advisors are available to schedule a Perwalian. Please contact support.';
            }
            if (message.includes('You must be logged in as kemahasiswaan')) {
                return 'You need to be logged in as a Kemahasiswaan user to schedule a Perwalian.';
            }
            return message;
        }
    </script>

    <style>
        /* Custom styling untuk input */
        .custom-input {
            background-color: #F5F5F5;
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            color: #757575;
        }

        /* Custom styling untuk dropdown */
        .custom-select {
            background-color: #F5F5F5;
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
            color: #757575;
        }

        /* Custom styling untuk tombol */
        .custom-btn {
            background-color: #2E7D32;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
        }

        .custom-btn:hover {
            background-color: #1B5E20;
        }

        /* Menyesuaikan label */
        .form-label {
            font-size: 14px;
            color: #424242;
            margin-bottom: 6px;
        }
    </style>
@endsection