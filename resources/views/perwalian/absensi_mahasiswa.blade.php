@extends('layouts.app')

@section('content')

<!-- Header -->
    <div class="d-flex align-items-center mb-4 border-bottom-line">
        <h3 class="me-auto">
        <a> Absensi Mahasiswa</a> 
        </h3>
        <a href="#" onclick="confirmLogout()">
        <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
        </a>
    </div>


    <div class="container">

        <!-- Class Selection Buttons -->
        <div class="am-class-buttons">
            <a href="{{ route('absensi.show', ['date' => '2025-02-20', 'class' => 'IF1']) }}" class="am-class-btn">
                Senin, 20 Februari 2025 (13 IF1)
            </a>
            <a href="{{ route('absensi.show', ['date' => '2025-02-21', 'class' => 'IF2']) }}" class="am-class-btn">
                Selasa, 21 Februari 2025 (13 IF2)
            </a>
        </div>
    </div>

    <script>
        function goBack() {
            window.history.back(); // Goes to the previous page
        }
    </script>
@endsection