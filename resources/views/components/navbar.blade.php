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
                <a href="#" class="text-decoration-none" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell fs-5 cursor-pointer" title="Notifications"></i>
                    @if(isset($notifications) && $notifications->count() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $notifications->count() }}
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    @endif
                </a>
                <ul class="dropdown-menu" aria-labelledby="notificationDropdown">
                    <li><h6 class="dropdown-header">Notifikasi</h6></li>
                    @php
                        // Determine the role
                        $userRole = session('user')['role'] ?? null;
                        // Default route for mahasiswa
                        $notificationRoute = $userRole === 'mahasiswa' ? route('mahasiswa_perwalian') : null;
                    @endphp
                    @forelse ($notifications ?? [] as $notif)
                        @php
                            $notifData = $notif->data ?? [];
                            $type = $notifData['extra_data']['type'] ?? null;
                        @endphp
                        @if($type)
                            @php
                                $label = strtoupper($type);
                                $link = match($type) {
                                    'konseling' => route('mahasiswa_konseling'),
                                    'perwalian' => route('mahasiswa_perwalian'),
                                    default => route('mahasiswa_perwalian'),
                                };
                                $badgeClass = match($type) {
                                    'konseling' => 'bg-success',
                                    'perwalian' => 'bg-primary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <li>
                                <a class="dropdown-item" href="{{ $link }}">
                                    <span class="badge {{ $badgeClass }} me-1">[{{ $label }}]</span>
                                    {{ $notifData['message'] ?? 'No message' }}
                                </a>
                            </li>
                        @else
                            <li>
                                @if($userRole === 'mahasiswa')
                                    <a class="dropdown-item" href="{{ $notificationRoute }}">
                                        {{ $notif->Pesan ?? 'No message' }} by {{ $notif->nama ?? 'Unknown' }}
                                    </a>
                                @else
                                    <span class="dropdown-item">
                                        {{ $notif->Pesan ?? 'No message' }}
                                    </span>
                                @endif
                            </li>
                        @endif
                    @empty
                        <li>
                            @if($userRole === 'mahasiswa')
                                <a class="dropdown-item" href="{{ $notificationRoute }}">Tidak ada notifikasi</a>
                            @else
                                <span class="dropdown-item">Tidak ada notifikasi</span>
                            @endif
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="logout">
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); confirmLogout();">
                    <i class="fas fa-sign-out-alt fs-5 cursor-pointer" title="Logout"></i>
                </a>
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
                window.location.href = "{{ route('logout') }}";
            }
        }
    </script>
@endif