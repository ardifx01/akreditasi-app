<div class="sidebar shadow-lg p-0 overflow-hidden"
    style="background: linear-gradient(135deg, #4b6cb7, #182848); height: 100%;">
    <!-- Header/Logo Area -->
    <div class="sidebar-header p-3 text-center" style="background-color: rgba(0,0,0,0);">
        <img src="{{ asset('img/logo.png') }}" alt="Logo" class="img-fluid mt-4" style="width: 200px; height: auto;">
    </div>

    <!-- User Profile Summary -->
    <div class="text-center py-3">
        <div class="avatar-circle mx-auto mb-2"
            style="width: 60px; height: 60px; border-radius: 50%; background-color: #ffffff33; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-user text-white" style="font-size: 24px;"></i>
        </div>
        <h6 class="text-white mb-0">Admin Perpustakaan</h6>
        <small class="text-white-50">Administrator</small>
    </div>

    <!-- Navigation Menu -->
    <div class="px-3 pb-3 mt-2">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3 {{ request()->is('/') ? 'active' : '' }}"
                    href="{{ route('dashboard') }}" style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-home me-3"></i>
                    <span>Dashboard</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50" style="font-size: 12px;"></i>
                </a>
            </li>

            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3" data-bs-toggle="collapse"
                    href="#sdmCollapse" style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-users me-3"></i>
                    <span>Data SDM</span>
                </a>
                <div class="collapse" id="sdmCollapse">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" href="{{ route('staff.index') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-id-card me-2"></i>Master Data Staff
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" href="{{ route('ijazah.index') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-graduation-cap me-2"></i>Data Ijazah
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" href="{{ route('transkrip.index') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-file-alt me-2"></i>Data Transkrip
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" href="{{ route('pelatihan.index') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-chalkboard-teacher me-2"></i>Data Pelatihan
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" href="{{ route('skp.index') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-tasks me-2"></i>Data SKP
                            </a>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" href="{{ route('sertifikasi.index') }}"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-certificate me-2"></i>Data Sertifikasi
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3" data-bs-toggle="collapse"
                    href="#daftarPustakaCollapse" style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-book me-3"></i>
                    <span>Daftar Pustaka</span>
                </a>
                <div class="collapse" id="daftarPustakaCollapse">
                    <ul class="nav flex-column ms-3 mt-2">
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" data-bs-toggle="collapse"
                                href="#statistikKoleksiCollapse"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-chart-pie me-2"></i>Statistik Koleksi
                            </a>
                            <div class="collapse" id="statistikKoleksiCollapse">
                                <ul class="nav flex-column ms-3 mt-1">
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3" href="#"
                                            style="color: #ccc; font-size: 0.85rem; transition: all 0.3s;">
                                            <i class="fas fa-graduation-cap me-2"></i>Per Prodi
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item mb-1">
                            <a class="nav-link rounded py-2 px-3" data-bs-toggle="collapse" href="#jenisKoleksiCollapse"
                                style="color: #ddd; font-size: 0.9rem; transition: all 0.3s;">
                                <i class="fas fa-layer-group me-2"></i>Jenis Koleksi
                            </a>
                            <div class="collapse" id="jenisKoleksiCollapse">
                                <ul class="nav flex-column ms-3 mt-1">
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3" href="#"
                                            style="color: #ccc; font-size: 0.85rem; transition: all 0.3s;">
                                            <i class="fas fa-journal-whills me-2"></i>E-Journal
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3" href="#"
                                            style="color: #ccc; font-size: 0.85rem; transition: all 0.3s;">
                                            <i class="fas fa-tablet-alt me-2"></i>E-Book
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3" href="#"
                                            style="color: #ccc; font-size: 0.85rem; transition: all 0.3s;">
                                            <i class="fas fa-book me-2"></i>Buku Fisik
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link rounded py-1 px-3" href="#"
                                            style="color: #ccc; font-size: 0.85rem; transition: all 0.3s;">
                                            <i class="fas fa-newspaper me-2"></i>Repository
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3 {{ request()->is('/') ? 'active' : '' }}"
                    href="#" style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-walking me-3"></i>
                    <span>Data Peminjaman</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50" style="font-size: 12px;"></i>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link d-flex align-items-center rounded py-2 px-3 {{ request()->is('/') ? 'active' : '' }}"
                    href="#" style="color: #fff; transition: all 0.3s;">
                    <i class="fas fa-book-reader me-3"></i>
                    <span>Data Peminjaman</span>
                    <i class="fas fa-chevron-right ms-auto opacity-50" style="font-size: 12px;"></i>
                </a>
            </li>




        </ul>
    </div>

</div>
{{-- <!-- Footer -->
<div class="sidebar-footer position-absolute bottom-0 start-0 end-0 p-3" style="background-color: rgba(0,0,0,0.2);">
    <form method="POST" action="{{ route('logout') }}" id="logout-form">
        @csrf
        <a class="d-flex align-items-center text-decoration-none" href="javascript:void(0)"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color: #fff;">
            <i class="fas fa-sign-out-alt me-2"></i>
            <span>Logout</span>
        </a>
    </form>
</div> --}}
