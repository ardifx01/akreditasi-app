<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Akreditasi Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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

        .nav-link:hover, .nav-link:focus {
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

        .dropdown-item:hover, .dropdown-item:focus {
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
        <div class="content flex-grow-1 p-4">
            <header class="mb-4">
                <h2>@yield('title')</h2>
            </header>
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
