@extends('layouts.app')

@section('content')
    <div class="container-fluid">
    
        <!-- Success Popup -->
        <div id="successPopup" class="alert alert-success" style="display: none; position: fixed; top: 20px; right: 20px; z-index: 1000;">
            Successfully made Perwalian
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
            const formData = new FormData(this);

            fetch("{{ route('kemahasiswaan_perwalian.store') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success popup
                    const popup = document.getElementById('successPopup');
                    popup.style.display = 'block';
                    setTimeout(() => {
                        popup.style.display = 'none';
                    }, 3000); // Fade away after 3 seconds
                } else {
                    // Display error messages
                    document.getElementById('jadwalMulaiError').textContent = data.errors?.jadwalMulai || data.message || '';
                    document.getElementById('jadwalSelesaiError').textContent = data.errors?.jadwalSelesai || '';
                    document.getElementById('keteranganError').textContent = data.errors?.keterangan || '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('jadwalMulaiError').textContent = 'An unexpected error occurred.';
            });
        });
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