@extends('layouts.app')

@section('content')
    <div class="container">
        
        <!-- Feedback Messages -->
        @if (session('success'))
            <div id="feedbackMessage" class="alert alert-dismissible fade show alert-success" role="alert">
                <span id="feedbackText">{{ session('success') }}</span>
                <button type="button" class="btn-close" onclick="hideFeedback()"></button>
            </div>
        @endif
        @if (session('error'))
            <div id="feedbackMessage" class="alert alert-dismissible fade show alert-danger" role="alert">
                <span id="feedbackText">{{ session('error') }}</span>
                <button type="button" class="btn-close" onclick="hideFeedback()"></button>
            </div>
        @endif
        <div id="feedbackMessage" class="alert alert-dismissible fade show d-none" role="alert">
            <span id="feedbackText"></span>
            <button type="button" class="btn-close" onclick="hideFeedback()"></button>
        </div>

        <!-- Class and Perwalian Buttons -->
        <div class="am-class-buttons">
            @forelse ($presentedPerwalians as $perwalian)
                <a href="{{ route('berita_acara.create', ['date' => $perwalian['date'], 'class' => $perwalian['class'], 'angkatan' => $perwalian['angkatan']]) }}"
                   class="am-class-btn">
                    Kelas {{ $perwalian['display'] }}
                </a>
            @empty
                <p class="text-muted">Tidak ada perwalian selesai yang tersedia untuk membuat berita acara.</p>
            @endforelse
        </div>
    </div>

    <style>
        .am-class-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-left: 370px;
            align-items:center ;
            margin-top: 100px;
        }
        .am-class-btn {
            width: 150px;
            height: 130;
            align-items: center;
            padding: 15px 20px;
            background-color: #68B8EA;
            color: white;
            font-size: large;
            font-weight: 100;
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
            font-weight: 8;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .back-btn:hover {
            background-color: #4A9CD6;
        }
        .back-btn .arrow {
            font-size: 40px;
            margin-right: 0px;
            line-height: 1;
        }
        #feedbackMessage.alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        #feedbackMessage.alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>

    <script>
        function goBack() {
            window.history.back();
        }

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

        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = "{{ route('logout') }}";
            }
        }
    </script>
@endsection