@if(isset($breadcrumbs) || isset($notifications))
    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <div class="breadcrumb-container">
            <ol class="breadcrumb">
                @foreach ($breadcrumbs as $breadcrumb)
                    @if ($breadcrumb['url'])
                        <li class="breadcrumb-item">
                            <a href="{{ $breadcrumb['url'] }}">{!! $breadcrumb['name'] !!}</a>
                        </li>
                    @else
                        <li class="breadcrumb-item active" aria-current="page">
                            {!! $breadcrumb['name'] !!}
                        </li>
                    @endif
                @endforeach
            </ol>
        </div>

        <div class="nav-icons">
            <div class="dropdown position-relative me-3">
                <a href="#" class="text-decoration-none" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                    <i class="fas fa-bell fs-5 cursor-pointer" title="Notifications"></i>
                    @if(isset($notifications) && $notifications->count() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-count">
                            {{ $notifications->count() }}
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu" aria-labelledby="notificationDropdown">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    @php
                        $userRole = auth()->user()->role ?? null;
                    @endphp
                    @forelse ($notifications as $notif)
                        @php
                            $notifData = $notif->data;
                            $type = $notifData['extra_data']['type'] ?? null;
                            $label = $type ? strtoupper($type) : 'INFO';
                            $link = $notifData['extra_data']['link'] ?? route('mahasiswa_perwalian');

                            $badgeClass = match($type) {
                                'konseling' => 'bg-success',
                                'perwalian' => 'bg-primary',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <li>
                            <a class="dropdown-item notification-link" href="{{ $link }}" data-notification-id="{{ $notif->id }}">
                                <span class="badge {{ $badgeClass }} me-1">[{{ $label }}]</span>
                                {{ $notif->data['message'] ?? 'No message' }}
                            </a>
                        </li>
                    @empty
                        <li>
                            <a class="dropdown-item" href="#">Tidak ada notifikasi</a>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="logout">
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <a href="#" onclick="event.preventDefault(); confirmLogout();">
                        <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
                    </a>
                </form>
            </div>
        </div>
    </nav>

    <!-- Styles remain unchanged -->
    <style>
        .breadcrumb-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: nowrap;
            background-color: #3282B8;
            color: white;
            padding: 15px 30px;
            gap: 10px;
        }
        .breadcrumb-container {
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
        }
        .breadcrumb {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            margin-bottom: 0;
            padding-left: 0;
            background: transparent;
            gap: 5px;
            overflow-x: auto;
            white-space: nowrap;
        }
        .breadcrumb-item {
            font-size: 18px;
            white-space: nowrap;
        }
        .breadcrumb-item a {
            color: white;
            text-decoration: none;
            font-weight: 300;
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
        }
        .breadcrumb-item.active {
            color: #fff;
        }
        .breadcrumb-item a:hover {
            color: #fff;
        }
        .nav-icons {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .nav-icons i {
            color: #fff;
        }
        .logout {
            display: flex;
            align-items: center;
        }
        .logout a {
            color: white;
            text-decoration: none;
        }
        .logout i {
            font-size: 20px;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .logout a:hover i {
            color: #ddd;
        }
        .dropdown.me-3 {
            position: relative;
        }
        @media (max-width: 768px) {
            .breadcrumb-nav {
                flex-direction: row;
                align-items: center;
                padding: 10px 20px;
                overflow-x: auto;
            }
            .breadcrumb-container {
                flex: 1 1 auto;
                min-width: 0;
            }
            .nav-icons {
                gap: 20px;
                flex-shrink: 0;
            }
            .dropdown.me-3 {
                margin-right: 15px;
            }
        }
    </style>

    <script>
        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                document.getElementById('logout-form').submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const notificationLinks = document.querySelectorAll('.notification-link');
            notificationLinks.forEach(link => {
                link.addEventListener('click', function (event) {
                    event.preventDefault();
                    const notificationId = this.getAttribute('data-notification-id');
                    const url = this.getAttribute('href');

                    // Send AJAX request to mark notification as read
                    fetch("{{ route('notifications.markAsRead', ['id' => '__ID__']) }}".replace('__ID__', notificationId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update the badge count
                            const badge = document.getElementById('notification-count');
                            let count = parseInt(badge.textContent) - 1;
                            if (count <= 0) {
                                badge.remove();
                            } else {
                                badge.textContent = count;
                            }
                            // Navigate to the link
                            window.location.href = url;
                        } else {
                            console.error('Failed to mark notification as read:', data.message);
                            // Still navigate to the link even if marking fails
                            window.location.href = url;
                        }
                    })
                    .catch(error => {
                        console.error('Error marking notification as read:', error);
                        // Navigate to the link even if the request fails
                        window.location.href = url;
                    });
                });
            });
        });
    </script>
@endif