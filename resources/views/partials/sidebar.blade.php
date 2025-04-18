<div class="sidebar p-3">
    <h4 class="text-center">Menu</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fas fa-home me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#sdmCollapse">
                <i class="fas fa-chart-bar me-2"></i>Data SDM
            </a>
            <div class="collapse" id="sdmCollapse">
                <ul class="nav flex-column submenu">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('staff.index') }}">
                            <i class="fas fa-calendar-day me-2"></i>Master Data Staff
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('ijazah.index') }}">
                            <i class="fas fa-graduation-cap me-2"></i>Data Ijazah
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-graduation-cap me-2"></i>Data Transkrip
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-graduation-cap me-2"></i>Data Pelatihan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-graduation-cap me-2"></i>Data SKP
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-graduation-cap me-2"></i>Data Sertifikasi
                        </a>
                    </li>
                </ul>
            </div>
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
                            <i class="fas fa-graduation-cap me-2"></i>Peminjaman Prodi
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    </ul>
</div>
