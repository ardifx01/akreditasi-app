<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Akreditasi Perpustakaan</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/logo4.png') }}" type="image/x-icon">

    {{-- CSS LINKS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    {{-- Menggunakan Font Awesome 6 untuk ikon yang lebih modern --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">


    @stack('styles') {{-- Untuk CSS tambahan dari halaman anak --}}

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background-color: #212529;
            color: #f8f9fa;
            transition: all 0.3s ease-in-out;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1050;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
            transform: translateX(0);
        }

        .sidebar-header {
            min-height: 120px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 20px !important;
        }

        .sidebar-logo {
            max-width: 180px;
            height: auto;
            display: block;
        }

        .sidebar-menu {
            padding-top: 20px;
        }

        .sidebar .nav-link {
            color: #e9ecef;
            font-size: 0.95rem;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            /* Ukuran ikon */
            min-width: 30px;
        }

        .sidebar .nav-link .nav-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar .nav-link .nav-arrow {
            margin-left: auto;
            font-size: 0.8rem;
            transition: transform 0.2s ease-in-out;
        }

        .sidebar .nav-link[aria-expanded="true"] .nav-arrow {
            transform: rotate(90deg);
        }

        .sidebar .collapse .nav-link {
            padding-left: 45px;
            font-size: 0.875rem;
            color: #ced4da;
        }

        .sidebar .collapse .nav-link:hover,
        .sidebar .collapse .nav-link.active {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .sidebar .collapse .nav-link i {
            font-size: 0.95rem;
            min-width: 25px;
        }

        .sidebar .collapse .nav-arrow-small {
            font-size: 0.7rem;
            margin-left: auto;
            transition: transform 0.2s ease-in-out;
        }

        .sidebar .collapse .nav-link[aria-expanded="true"] .nav-arrow-small {
            transform: rotate(90deg);
        }

        /* Main Content Area */
        .main-wrapper {
            display: flex;
            min-height: 100vh;
            width: 100%;
            padding-left: 260px;
            transition: padding-left 0.3s ease-in-out;
        }

        .content-area {
            flex-grow: 1;
            padding: 20px;
            background-color: #f8f9fa;
        }

        /* Navbar untuk toggle di mobile */
        .navbar-top {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 10px 20px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-toggler-custom {
            border: none;
            color: #212529;
            font-size: 1.5rem;
        }

        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1045;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }

        .sidebar-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive Adjustments */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 0 0 40px rgba(0, 0, 0, 0.3);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                padding-left: 0;
            }

            .navbar-top {
                display: flex;
            }
        }

        @media (min-width: 992px) {
            .navbar-top {
                display: none;
            }
        }

        /* Footer */
        .app-footer {
            background-color: #f0f2f5;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: auto;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        {{-- Sidebar --}}
        <div id="sidebar" class="sidebar">
            @include('partials.sidebar')
        </div>

        {{-- Main Content Area --}}
        <div class="content-area d-flex flex-column">
            {{-- Top Navbar (untuk mobile toggle) --}}
            <nav class="navbar-top d-lg-none sticky-top">
                <button class="navbar-toggler-custom" type="button" id="toggleSidebarBtn"
                    aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="navbar-brand-mobile ms-auto">
                    <img src="{{ asset('img/logo4.png') }}" alt="Logo" style="height: 35px;">
                </div>
            </nav>

            <header class="d-flex justify-content-between align-items-center mb-4 pt-3">
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

            <main class="flex-grow-1 mb-7">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </main>

            @include('partials.footer')
        </div>
    </div>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    {{-- JS LINKS --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script>
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

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        });

        // Script untuk sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('toggleSidebarBtn');
            const closeBtn = document.getElementById('closeSidebarBtn');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const mainWrapper = document.querySelector('.main-wrapper');

            function toggleSidebar() {
                sidebar.classList.toggle('show');
                sidebarBackdrop.classList.toggle('show');
                if (sidebar.classList.contains('show') && window.innerWidth < 992) {
                    document.body.style.overflow = 'hidden';
                } else {
                    document.body.style.overflow = '';
                }
            }

            function closeSidebar() {
                sidebar.classList.remove('show');
                sidebarBackdrop.classList.remove('show');
                document.body.style.overflow = '';
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', closeSidebar);
            }

            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', closeSidebar);
            }

            function handleResize() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                    document.body.style.overflow = '';
                    mainWrapper.style.paddingLeft = '0';
                } else {
                    sidebar.classList.add('show');
                    sidebarBackdrop.classList.remove('show');
                    document.body.style.overflow = '';
                    mainWrapper.style.paddingLeft = '260px';
                }
            }

            handleResize();
            window.addEventListener('resize', handleResize);

            // Handle collapse arrows for nested menus
            document.querySelectorAll('.sidebar .nav-link[data-bs-toggle="collapse"]').forEach(function(element) {
                element.addEventListener('click', function() {
                    const targetId = this.getAttribute('href');
                    const targetCollapse = document.querySelector(targetId);
                    if (targetCollapse && targetCollapse.classList.contains('show')) {
                        this.querySelector('.nav-arrow, .nav-arrow-small').style.transform =
                            'rotate(0deg)';
                    } else {
                        this.querySelector('.nav-arrow, .nav-arrow-small').style.transform =
                            'rotate(90deg)';
                    }
                });
            });

            document.querySelectorAll('.sidebar .collapse.show').forEach(function(collapseElement) {
                const parentLink = document.querySelector(
                    `[data-bs-toggle="collapse"][href="#${collapseElement.id}"]`);
                if (parentLink) {
                    parentLink.querySelector('.nav-arrow, .nav-arrow-small').style.transform =
                        'rotate(90deg)';
                }
            });

        });
    </script>
</body>

</html>
