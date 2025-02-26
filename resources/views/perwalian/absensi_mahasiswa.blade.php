@extends('layouts.app')

@section('content')
    <div class="am-container">
        <!-- Header -->
        <div class="am-header">
            <button onclick="goBack()" class="am-back-btn">← Back</button>
            <h1>Absensi Mahasiswa</h1>
        </div>

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