<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Akreditasi Perpustakaan</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/logo3.png') }}" type="image/x-icon">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')

    <style>
        /* Custom Styles */
        .navbar {
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }

        .nav-link {
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .nav-link:hover,
        .nav-link:focus {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: 500;
        }

        .dropdown-menu {
            border: none;
            border-radius: 0.5rem;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: #f8f9fa;
            color: #182848;
        }

        .avatar-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .dropdown-mega {
            padding: 0 !important;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .navbar-expand-lg .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
                margin: 0 0.25rem;
            }
        }
    </style>
</head>

<body>
    <div class="d-flex">
        @include('partials.sidebar')
        {{-- @include('partials.navbar') --}}
        <div class="content flex-grow-1 p-4">
            <header class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">@yield('title')</h2>

                @if (Auth::check())
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </button>
                    </form>
                @endif
            </header>
            <main>
                @yield('content')
            </main>
        </div>

    </div>
    <script>
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                document.getElementById('loader').style.display = 'block';
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>

    @stack('scripts')
</body>

</html>
