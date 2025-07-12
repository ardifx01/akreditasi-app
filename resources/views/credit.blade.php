@extends('layouts.app')

@section('title', 'Developers')

@section('content')
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Pengembang Aplikasi Web Akreditasi Support</h4>
        </div>
        <div class="card-body">
            <p class="lead">Aplikasi ini dikembangkan dengan dedikasi oleh tim berikut:</p>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center bg-light p-3 rounded shadow-sm">
                        <i class="fas fa-user-circle fa-3x text-info me-3"></i>
                        <div>
                            <h5 class="mb-0">Muhammad Asharul Maali, S.Kom</h5>
                            <p class="text-muted mb-0">Peran: Lead Developer</p>
                            <a href="#" class="text-primary small" target="_blank"><i
                                    class="fab fa-github me-1"></i>GitHub</a>
                            <a href="#" class="text-info small ms-2" target="_blank"><i
                                    class="fab fa-linkedin me-1"></i>LinkedIn</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center bg-light p-3 rounded shadow-sm">
                        <i class="fas fa-user-circle fa-3x text-success me-3"></i>
                        <div>
                            <h5 class="mb-0">Ammar Miftahudin Anshori</h5>
                            <p class="text-muted mb-0">Peran: Web Developer / UI/UX</p>
                            <a href="#" class="text-primary small" target="_blank"><i
                                    class="fab fa-github me-1"></i>GitHub</a>
                            <a href="#" class="text-info small ms-2" target="_blank"><i
                                    class="fab fa-linkedin me-1"></i>LinkedIn</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center bg-light p-3 rounded shadow-sm">
                        <i class="fas fa-user-circle fa-3x text-warning me-3"></i>
                        <div>
                            <h5 class="mb-0">Khoiruddin Nur Wahid, S.Pd</h5>
                            <p class="text-muted mb-0">Peran: Database Administrator / Support</p>
                            <a href="#" class="text-primary small" target="_blank"><i
                                    class="fab fa-github me-1"></i>GitHub</a>
                            <a href="#" class="text-info small ms-2" target="_blank"><i
                                    class="fab fa-linkedin me-1"></i>LinkedIn</a>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center bg-light p-3 rounded shadow-sm">
                        <i class="fas fa-user-circle fa-3x text-danger me-3"></i>
                        <div>
                            <h5 class="mb-0">[Nama Developer 4]</h5>
                            <p class="text-muted mb-0">Peran: Dokumentasi / Support</p>
                            <a href="#" class="text-primary small" target="_blank"><i
                                    class="fab fa-github me-1"></i>GitHub</a>
                            <a href="#" class="text-info small ms-2" target="_blank"><i
                                    class="fab fa-linkedin me-1"></i>LinkedIn</a>
                        </div>
                    </div>
                </div> --}}
            </div>

            <hr class="my-4">

            <h5>Ucapan Terima Kasih</h5>
            <p>Kami mengucapkan terima kasih kepada semua pihak yang telah berkontribusi dan memberikan dukungan dalam
                pengembangan aplikasi ini, termasuk:</p>
            <ul>
                <li>Allah Swt</li>
                <li>Tim Perpustakaan</li>
                <li>Komunitas Open Source (Laravel, Bootstrap, Font Awesome, jQuery, Chart.js, DataTables)</li>
            </ul>

            <p class="mt-4 text-center text-muted">Aplikasi ini dibuat dengan &#x2764;&#xfe0f; Indonesia.</p>
        </div>
    </div>
@endsection
