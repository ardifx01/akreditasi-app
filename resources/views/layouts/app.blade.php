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
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3">
            <h4 class="text-center">Menu</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#kunjunganCollapse">
                        <i class="fas fa-chart-bar me-2"></i>Data Kunjungan
                    </a>
                    <div class="collapse" id="kunjunganCollapse">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-calendar-day me-2"></i>Kunjungan Hari Ini
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('kunjungan-fakultas') }}">
                                    <i class="fas fa-university me-2"></i>Kunjungan Fakultas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-graduation-cap me-2"></i>Kunjungan Prodi
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#daftarPustakaCollapse">
                        <i class="fas fa-book me-2"></i>Daftar Pustaka
                    </a>
                    <div class="collapse" id="daftarPustakaCollapse">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="collapse" href="#statistikKoleksiCollapse">
                                    <i class="fas fa-chart-pie me-2"></i>Statistik Koleksi
                                </a>
                                <div class="collapse" id="statistikKoleksiCollapse">
                                    <ul class="nav flex-column sub-submenu">
                                        <li>
                                            <a class="nav-link" href="#">
                                                <i class="fas fa-university me-2"></i>Per Fakultas
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="#">
                                                <i class="fas fa-graduation-cap me-2"></i>Per Prodi
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="collapse" href="#jenisKoleksiCollapse">
                                    <i class="fas fa-layer-group me-2"></i>Jenis Koleksi
                                </a>
                                <div class="collapse" id="jenisKoleksiCollapse">
                                    <ul class="nav flex-column sub-submenu">
                                        <li>
                                            <a class="nav-link" href="#">
                                                <i class="fas fa-journal-whills me-2"></i>E-Journal
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="#">
                                                <i class="fas fa-tablet-alt me-2"></i>E-Book
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="#">
                                                <i class="fas fa-book me-2"></i>Buku Fisik
                                            </a>
                                        </li>
                                        <li>
                                            <a class="nav-link" href="#">
                                                <i class="fas fa-newspaper me-2"></i>Repository
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#peminjamanCollapse">
                        <i class="fas fa-book-reader me-2"></i>Data Peminjaman
                    </a>
                    <div class="collapse" id="peminjamanCollapse">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-calendar-day me-2"></i>Peminjaman Hari Ini
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-university me-2"></i>Peminjaman Fakultas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">
                                    <i class="fas fa-graduation-cap me-2"></i>Peminjaman Prodi
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        <div class="content flex-grow-1 p-4">
            <header class="mb-4">
                <h2>@yield('title')</h2>
            </header>
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
