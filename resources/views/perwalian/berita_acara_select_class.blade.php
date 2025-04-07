@extends('layouts.app')

@section('content')
    

    <div class="container">
        <!-- Back Button -->
        <div class="mb-3">
            <button onclick="goBack()" class="btn btn-secondary">⟵ Back</button>
        </div>

        <!-- Feedback Messages -->
        <div id="feedbackMessage" class="alert alert-dismissible fade show d-none" role="alert">
            <span id="feedbackText"></span>
            <button type="button" class="btn-close" onclick="hideFeedback()"></button>
        </div>

        <!-- Class and Perwalian Buttons -->
        <div class="am-class-buttons">
            @forelse ($completedPerwalians as $perwalian)
                <a href="{{ route('berita_acara.create', ['kelas' => $perwalian['class'], 'tanggal_perwalian' => $perwalian['date'], 'angkatan' => $perwalian['angkatan'] ]) }}"
                   class="am-class-btn">
                    {{ $perwalian['display'] }}
                </a>
            @empty
                <p class="text-muted">Tidak ada perwalian selesai yang tersedia untuk membuat berita acara.</p>
            @endforelse
        </div>
    </div>

    <style>
        .border-bottom-line {
            border-bottom: 1px solid #E5E5E5;
            padding-bottom: 10px;
        }
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
            transition: background-color 0.3s ease;
        }
        .am-class-btn:hover {
            background-color: #0056b3;
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