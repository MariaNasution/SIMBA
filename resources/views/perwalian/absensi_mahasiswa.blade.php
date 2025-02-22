@extends('layouts.app')

@section('content')
    <style>
        /* General styling for layout */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        /* Header styling */
        .header {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .back-btn {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .back-btn:hover {
            opacity: 0.9;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin: 0;
            font-weight: bold;
        }

        /* Class buttons styling */
        .class-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
        }

        .class-btn {
            background-color: #3498db;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-align: left;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .class-btn:hover {
            opacity: 0.9;
        }
    </style>

    <!-- Header -->
    <div class="header">
        <button onclick="goBack()" class="back-btn">← Back</button>
        <h1>Absensi Mahasiswa</h1>
    </div>

    <!-- Class Selection Buttons -->
    <div class="class-buttons">
        <a href="{{ route('absensi.show', ['date' => '2025-02-20', 'class' => 'IF1']) }}" class="class-btn">
            Senin, 20 Februari 2025 (13 IF1)
        </a>
        <a href="{{ route('absensi.show', ['date' => '2025-02-21', 'class' => 'IF2']) }}" class="class-btn">
            Selasa, 21 Februari 2025 (13 IF2)
        </a>
    </div>

    <script>
        function goBack() {
            window.history.back(); // Goes to the previous page
        }
    </script>
@endsection