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
                    @forelse ($notifications ?? [] as $notif)
                        <li><a class="dropdown-item" href="#">{{ $notif->Pesan ?? 'No message' }}</a></li>
                    @empty
                        <li><a class="dropdown-item" href="#">Tidak ada notifikasi</a></li>
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
    
    <style>
        .breadcrumb-nav {
            color: white;
            background-color: #3282B8;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: nowrap; /* Prevent wrapping unless necessary */
            width: 100%;
            gap: 20px; /* Add spacing between flex children */
        }

        .breadcrumb-container {
            display: flex;
            align-items: center;
            flex-wrap: wrap; /* Allow breadcrumbs to wrap if too long */
            flex-grow: 1; /* Allow it to take available space */
            min-width: 0; /* Prevent overflow issues */
        }

        .breadcrumb {
            margin-bottom: 0;
            background: transparent;
            padding-left: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }

        .breadcrumb-item {
            font-size: 18px;
            white-space: nowrap; /* Prevent breadcrumb text from wrapping */
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
            flex-shrink: 0; /* Prevent icons from shrinking */
            gap: 15px; /* Consistent spacing between icons */
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
                flex-direction: column;
                align-items: flex-start; /* Align items to the start on mobile */
                padding: 10px 20px; /* Reduce padding on smaller screens */
            }

            .breadcrumb-container {
                justify-content: center;
                width: 100%;
                margin-bottom: 10px;
            }

            .nav-icons {
                justify-content: center;
                width: 100%;
                gap: 20px; /* Increase gap for better touch targets on mobile */
            }

            .dropdown.me-3 {
                margin-right: 0; /* Remove right margin on mobile */
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