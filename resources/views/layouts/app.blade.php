<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Akreditasi Perpustakaan</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/logo3.png') }}" type="image/x-icon">

    {{-- CSS LINKS (biarkan di head) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" />

    @stack('styles') {{-- Untuk CSS tambahan dari halaman anak --}}

    <style>
        body {
            font-family: 'Inter', sans-serif;
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

        .sidebar {
            width: 250px;
            transition: transform 0.3s ease;
            z-index: 1040;
            background-color: #212529;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        @media (min-width: 992px) {
            .sidebar {
                transform: translateX(0) !important;
                position: relative;
                height: auto;
            }

            #toggleSidebarBtn {
                display: none;
            }
        }

        .main-content {
            flex-grow: 1;
            padding: 1.5rem;
        }

        /* Fixed footer */
        footer {
            background-color: #f8f9fa;
            padding: 1rem 0;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="d-flex min-vh-100">
        <div id="sidebar" class="sidebar bg-dark text-white p-3">
            @include('partials.sidebar')
        </div>
        <div class="contents flex-grow-1 p-4">
            <header class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title"><span id="current-time"></span></h5>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt me-1"></i> Logout
                        </button>
                    </form>
                @endauth
            </header>
            <div class="content-wrapper">
                <main class="row">
                    <div class="container">
                        @yield('content')
                    </div>
                </main>
            </div>
            @include('partials.footer')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- 2. Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
    {{-- 3. Font Awesome --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" crossorigin="anonymous"></script>

    {{-- 4. DataTables Core (jQuery) --}}
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>

    {{-- 5. Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    @stack('scripts')
    <script>
        $(document).ready(function() {
            function updateTime() {
                const now = new Date();
                const dateOptions = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const timeOptions = {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                };
                const formattedDate = new Intl.DateTimeFormat('id-ID', dateOptions).format(now);
                const formattedTime = new Intl.DateTimeFormat('id-ID', timeOptions).format(now);
                document.getElementById('current-time').textContent = formattedDate + ' ' + formattedTime;
            }

            updateTime();
            setInterval(updateTime, 1000);
        });

        // Script untuk sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebarBtn');
            const closeBtn = document.getElementById('closeSidebarBtn');

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    sidebar.classList.add('collapsed');
                });
            }

            // Collapse sidebar by default on small screens
            if (window.innerWidth < 992) {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
</body>

</html>
