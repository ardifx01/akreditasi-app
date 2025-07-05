@extends('layouts.app')

@section('title', 'Data Ijazah')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <style>
        .search-form-container {
            margin-bottom: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
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

        <h1 class="mb-4">Data Ijazah</h1>

        <div class="d-flex justify-content-between align-items-center mb-3 search-form-container">
            <div>
                @can('admin-action')
                    <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#ijazahModal">
                        <i class="fas fa-plus me-2"></i> Tambah Data Ijazah
                    </button>
                    @include('modal.create-ijazah')
                @endcan

                {{-- Tombol Download All Dokumen --}}
                {{-- <a href="{{ route('ijazah.downloadAll') }}" class="btn btn-info">
                    <i class="fas fa-download me-2"></i> Download Semua Dokumen
                </a> --}}
            </div>

            {{-- Search Input Form --}}
            <form action="{{ route('ijazah.index') }}" method="GET" class="d-flex ms-auto">
                <input type="text" name="search" class="form-control me-2" placeholder="Cari ID, Judul Ijazah, Tahun..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
                @if (request('search'))
                    <a href="{{ route('ijazah.index') }}" class="btn btn-secondary ms-2">Reset</a>
                @endif
            </form>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover text-center" id="data-table-ijazah">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID Staff</th>
                                <th>Nama Ijazah</th>
                                <th>File Ijazah</th>
                                <th>Tahun</th>
                                @can('admin-action')
                                    <th>Aksi</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($ijazah as $no => $item)
                                <tr>
                                    <td>{{ $no + $ijazah->firstItem() }}</td>
                                    <td>{{ $item->id_staf }}</td>
                                    <td>{{ $item->judul_ijazah }}</td>
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
                                                data-bs-target="#editIjazah{{ $item->id }}">Edit
                                            </button>
                                            <form action="{{ route('ijazah.destroy', $item->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                                            </form>
                                        </td>
                                    @endcan
                                </tr>
                                @include('modal.view-pdf', ['item' => $item])
                                @include('modal.edit-ijazah', ['ijazah' => $item])
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->can('admin-action') ? '6' : '5' }}" class="text-center">
                                        Tidak ada data ijazah ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $ijazah->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@endpush
