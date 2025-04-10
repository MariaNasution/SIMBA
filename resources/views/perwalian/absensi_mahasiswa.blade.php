@extends('layouts.app')

@section('content')
    
    <div class="container">
    
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
            margin-left: 200px;
            margin-top: 100px

        }
        .am-class-btn {
            width: 650px;
            padding: 15px 20px;
            background-color: #68B8EA;
            color: white;
            font-size: large;
            font-weight: 100px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .am-class-btn:hover {
            background-color: #0056b3;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            background-color: #68B8EA;
            color: #fff;
            font-size: 18px;
            font-weight: 8px;

            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #4A9CD6; /* Slightly darker shade for hover */
        }

        .back-btn .arrow {
            font-size: 40px; /* Increased from 24px to 32px for a bigger arrow */
            margin-right: 0px; /* Space between arrow and "Back" text */
            line-height: 1; /* Ensure vertical alignment */
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