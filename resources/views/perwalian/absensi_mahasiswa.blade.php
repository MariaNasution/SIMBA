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
        <!-- Class Selection Buttons -->
        <div class="am-class-buttons">
            @foreach ($classes as $classItem)
                <a href="{{ route('absensi.show', ['date' => $classItem['date'], 'class' => $classItem['class']]) }}" class="am-class-btn">
                    {{ $classItem['display'] }}
                </a>
            @endforeach
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back(); // Goes to the previous page
        }
    </script>
@endsection