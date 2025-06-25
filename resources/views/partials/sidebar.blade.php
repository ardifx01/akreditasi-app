<div class="sidebar shadow-lg p-0 overflow-hidden bg-dark collapsed" id="sidebar">
    <!-- Header/Logo Area -->
    <div class="sidebar-header d-flex align-items-center justify-content-between p-3"
        style="background-color: rgba(0,0,0,0);">
        <!-- Logo -->
        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="img-fluid mt-5" style="width: 220px; height: auto;">
    </div>

    <!-- Navigation Menu -->
    <div class="px-3 pb-3 mt-5">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a data-bs-toggle="tooltip"
                    class="nav-link d-flex align-items-center rounded py-2 px-3 {{ request()->is('/') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}" style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-home me-4"></i>
                    <span>Dashboard</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50" style="font-size: 12px;"></i>
                </a>
            </li>

            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3" data-bs-toggle="collapse"
                    href="#sdmCollapse" style="color: #fff; transition: all 0.3s;"
                    aria-expanded="{{ request()->routeIs('staff.*') || request()->routeIs('ijazah.*') || request()->routeIs('transkrip.*') ? 'true' : 'false' }}">
                    <i class="fas fa-users "></i>
                    <span>Data SDM</span>
                </a>
                <div class="collapse {{ request()->routeIs('staff.*') || request()->routeIs('ijazah.*') || request()->routeIs('transkrip.*') || request()->routeIs('pelatihan.*') || request()->routeIs('skp.*') || request()->routeIs('sertifikasi.*') || request()->routeIs('mou.*') ? 'show' : '' }}"
                    id="sdmCollapse">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('staff.*') ? 'active' : '' }}"
                                href="{{ route('staff.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-id-card me-2"></i>Master Data Staff
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('ijazah.*') ? 'active' : '' }}"
                                href="{{ route('ijazah.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-graduation-cap me-2"></i>Data Ijazah
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('transkrip.*') ? 'active' : '' }}"
                                href="{{ route('transkrip.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-graduation-cap me-2"></i>Data Transkrip
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('pelatihan.*') ? 'active' : '' }}"
                                href="{{ route('pelatihan.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Data Pelatihan
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('skp.*') ? 'active' : '' }}"
                                href="{{ route('skp.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-tasks me-2"></i>Data SKP
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('sertifikasi.*') ? 'active' : '' }}"
                                href="{{ route('sertifikasi.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-certificate me-2"></i>Data Sertifikasi
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('mou.*') ? 'active' : '' }}"
                                href="{{ route('mou.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-handshake me-2"></i>Data MoU
                            </a>
                        </li>
                        {{-- <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('pelatihan.*') ? 'active' : '' }}"
                                href="{{ route('pelatihan.index') }}" style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Data Pelatihan
                            </a>
                        </li> --}}


                    </ul>
                </div>
            </li>


            <li class="nav-item mb-2">
                @php
                    $isDaftarKoleksiActive = request()->routeIs('koleksi.*');
                    $isStatistikActive = request()->routeIs('koleksi.prodi');
                    $isJenisActive =
                        request()->routeIs('koleksi.jurnal') ||
                        request()->routeIs('koleksi.ebook') ||
                        request()->routeIs('koleksi.textbook') ||
                        request()->routeIs('koleksi.prosiding') ||
                        request()->routeIs('koleksi.periodikal') ||
                        request()->routeIs('koleksi.referensi');
                @endphp

                <a class="nav-link d-flex align-items-center rounded py-2 px-3 {{ $isDaftarKoleksiActive ? 'active' : '' }}"
                    data-bs-toggle="collapse" href="#daftarPustakaCollapse"
                    aria-expanded="{{ $isDaftarKoleksiActive ? 'true' : 'false' }}"
                    style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-book me-3"></i>
                    <span>Daftar Koleksi</span>
                </a>

                <div class="collapse {{ $isDaftarKoleksiActive ? 'show' : '' }}" id="daftarPustakaCollapse">
                    <ul class="nav flex-column ms-3 mt-2">
                        {{-- Statistik Koleksi --}}
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ $isStatistikActive ? 'active' : '' }}"
                                data-bs-toggle="collapse" href="#statistikKoleksiCollapse"
                                style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-chart-pie me-2"></i>Statistik Koleksi
                            </a>
                            <div class="collapse {{ $isStatistikActive ? 'show' : '' }}" id="statistikKoleksiCollapse">
                                <ul class="nav flex-column ms-3 mt-1">
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3 {{ request()->routeIs('koleksi.prodi') ? 'active' : '' }}"
                                            href="{{ route('koleksi.prodi') }}"
                                            style="color: #ccc; font-size: 0.85rem;">
                                            <i class="fas fa-graduation-cap me-2"></i>Per Prodi
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- Jenis Koleksi --}}
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ $isJenisActive ? 'active' : '' }}"
                                data-bs-toggle="collapse" href="#jenisKoleksiCollapse"
                                style="color: #ddd; font-size: 0.9rem;">
                                <i class="fas fa-layer-group me-2"></i>Jenis Koleksi
                            </a>
                            <div class="collapse {{ $isJenisActive ? 'show' : '' }}" id="jenisKoleksiCollapse">
                                <ul class="nav flex-column ms-3 mt-1">
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3 {{ request()->routeIs('koleksi.jurnal') ? 'active' : '' }}"
                                            href="{{ route('koleksi.jurnal') }}"
                                            style="color: #ccc; font-size: 0.85rem;">
                                            <i class="fas fa-journal-whills me-2"></i>Journal
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3 {{ request()->routeIs('koleksi.ebook') ? 'active' : '' }}"
                                            href="{{ route('koleksi.ebook') }}"
                                            style="color: #ccc; font-size: 0.85rem;">
                                            <i class="fas fa-tablet-alt me-2"></i>E-Book
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3 {{ request()->routeIs('koleksi.textbook') ? 'active' : '' }}"
                                            href="{{ route('koleksi.textbook') }}"
                                            style="color: #ccc; font-size: 0.85rem;">
                                            <i class="fas fa-book me-2"></i>Text Book
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3 {{ request()->routeIs('koleksi.prosiding') ? 'active' : '' }}"
                                            href="{{ route('koleksi.prosiding') }}"
                                            style="color: #ccc; font-size: 0.85rem;">
                                            <i class="fas fa-newspaper me-2"></i>Prosiding
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3 {{ request()->routeIs('koleksi.periodikal') ? 'active' : '' }}"
                                            href="{{ route('koleksi.periodikal') }}"
                                            style="color: #ccc; font-size: 0.85rem;">
                                            <i class="fas fa-calendar-week me-2"></i>Periodical
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3 {{ request()->routeIs('koleksi.referensi') ? 'active' : '' }}"
                                            href="{{ route('koleksi.referensi') }}"
                                            style="color: #ccc; font-size: 0.85rem;">
                                            <i class="fas fa-newspaper me-2"></i>References
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>

            @php
                $isKunjunganActive =
                    request()->routeIs('kunjungan.prodiChart') || request()->routeIs('kunjungan.prodiTable');
            @endphp

            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3 {{ $isKunjunganActive ? 'active' : '' }}"
                    data-bs-toggle="collapse" href="#kunjunganCollapse"
                    aria-expanded="{{ $isKunjunganActive ? 'true' : 'false' }}"
                    style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-users me-3"></i>
                    <span>Data Kunjungan</span>
                </a>

                <div class="collapse {{ $isKunjunganActive ? 'show' : '' }}" id="kunjunganCollapse">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('kunjungan.prodiChart') ? 'active' : '' }}"
                                href="{{ route('kunjungan.prodiChart') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-chart-bar me-2"></i>Chart
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('kunjungan.prodiTable') ? 'active' : '' }}"
                                href="{{ route('kunjungan.prodiTable') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-table me-2"></i>Table
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3 {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" href="#peminjamanCollapse" style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-book-reader me-3"></i>
                    <span>Data Peminjaman</span>
                </a>
                <div class="collapse {{ request()->routeIs('peminjaman.*') ? 'show' : '' }}" id="peminjamanCollapse">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3 {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                                href="{{ route('dashboard') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-book-open me-2"></i>Daftar Peminjaman
                            </a>
                        </li>
                        {{-- Tambahkan item lain jika perlu --}}
                    </ul>
                </div>
            </li>

        </ul>
    </div>
</div>
