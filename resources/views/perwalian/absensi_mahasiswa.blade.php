@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
            <a href="{{ route('dosen.presensi') }}">Absensi Mahasiswa</a>
        </h3>
        <a href="#" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>

    <div class="container">
        <!-- Back Button -->
        <div class="mb-3">
            <button onclick="goBack()" class="btn btn-secondary">⟵ Back</button>
        </div>

        <!-- Class Selection Buttons -->
        <div class="am-class-buttons">
            @forelse ($classes as $classItem)
                <a href="{{ route('absensi.show', ['date' => $classItem['date'], 'class' => $classItem['class']]) }}" class="am-class-btn">
                    {{ $classItem['display'] }}
                </a>
            @empty
                <p>No classes available.</p>
            @endforelse
        </div>
    </div>

    <style>
        .am-class-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .am-class-btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .am-class-btn:hover {
            background-color: #0056b3;
        }
    </style>

    <script>
        function goBack() {
            window.history.back();
        }

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = "{{ route('logout') }}";
            }
        }
    </script>
@endsection