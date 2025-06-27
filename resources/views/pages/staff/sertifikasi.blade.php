@extends('layouts.app')

@section('title', 'Sertifikasi')

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <style>
        /* Optional: Adjust spacing for search form */
        .search-form-container {
            margin-bottom: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4"> {{-- Added mt-4 for top margin from navbar --}}
        {{-- Success and Error Alerts --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <h1 class="mb-4">Data Sertifikasi</h1>

        {{-- Action buttons and Search form --}}
        <div class="d-flex justify-content-between align-items-center mb-3 search-form-container">
            @can('admin-action')
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#sertifikasiModal">
                    <i class="fas fa-plus me-2"></i> Tambah Data Sertifikasi
                </button>
                @include('modal.create-sertifikasi')
            @endcan

            {{-- Search Input Form --}}
            <form action="{{ route('sertifikasi.index') }}" method="GET" class="d-flex ms-auto">
                <input type="text" name="search" class="form-control me-2"
                    placeholder="Cari ID, Nama Sertifikasi, Tahun..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
                @if (request('search'))
                    <a href="{{ route('sertifikasi.index') }}" class="btn btn-secondary ms-2">Reset</a>
                @endif
            </form>
        </div>

        <div class="card shadow mb-4"> {{-- Added card styling --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-center" id="data-table-sertifikasi">
                        {{-- Added ID for DataTables --}}
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Staff</th>
                                <th>Nama Sertifikasi</th>
                                <th>File Sertifikasi</th> {{-- Changed from Nama File to File Sertifikasi --}}
                                <th>Tahun</th>
                                @can('admin-action')
                                    <th>Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sertifikasi as $no => $item)
                                {{-- Changed to @forelse --}}
                                <tr>
                                    <td>{{ $no + $sertifikasi->firstItem() }}</td> {{-- Corrected for pagination index --}}
                                    <td>{{ $item->id_staf }}</td>
                                    <td>{{ $item->judul_sertifikasi }}</td>
                                    <td>
                                        <button class="btn btn-success btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#view-pdf-{{ $item->id }}">
                                            <i class="fas fa-eye me-1"></i> View Dokumen
                                        </button>
                                    </td>
                                    <td>{{ $item->tahun }}</td>
                                    @can('admin-action')
                                        <td>
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editSertifikasi{{ $item->id }}">Edit
                                            </button>
                                            <form action="{{ route('sertifikasi.destroy', $item->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                                {{-- Include the view-pdf and edit-sertifikasi modals for each item --}}
                                @include('modal.view-pdf', ['item' => $item]) {{-- Pass $item to view-pdf modal --}}
                                @include('modal.edit-sertifikasi', ['sertifikasi' => $item]) {{-- Pass $item as $sertifikasi to edit-sertifikasi modal --}}
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->can('admin-action') ? '6' : '5' }}" class="text-center">
                                        Tidak ada data sertifikasi ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $sertifikasi->links() }} {{-- Laravel Pagination Links --}}
                </div>
            </div>
        </div>
    </div>
@endsection
