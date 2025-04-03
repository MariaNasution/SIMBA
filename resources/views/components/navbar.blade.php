@if(isset($breadcrumbs))
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
        <div class="logout">
            <a href="{{ route('logout') }}" onclick="confirmLogout()">
                <i class="fas fa-sign-out-alt fs-5" title="Logout"></i>
            </a>
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
            flex-wrap: wrap;
            width: 100%;
        }

        .breadcrumb-container {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }

        .breadcrumb {
            margin-bottom: 0;
            background: transparent;
            padding-left: 0;
            display: flex;
            flex-wrap: wrap;
        }

        .breadcrumb-item {
            font-size: 18px;
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

        /* Logout button styling */
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

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .breadcrumb-nav {
                flex-direction: column;
                text-align: center;
            }
            .breadcrumb-container {
                justify-content: center;
                margin-bottom: 10px;
            }
            .logout {
                justify-content: center;
            }
        }
    </style>
@endif
