@extends('layouts.app')

@section('title', 'Staff')

@push('styles')
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

        <h1 class="mb-4">Data Staff</h1>

        {{-- Action buttons and Search form --}}
        <div class="d-flex justify-content-between align-items-center mb-3 search-form-container">
            @can('admin-action')
                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="fas fa-plus me-2"></i> Tambah Data Staff
                </button>
                @include('modal.create-staff')
            @endcan

            {{-- Search Input Form --}}
            <form action="{{ route('staff.index') }}" method="GET" class="d-flex ms-auto"> {{-- ms-auto pushes it to the right --}}
                <input type="text" name="search" class="form-control me-2" placeholder="Cari ID, Nama, Posisi..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
                @if (request('search'))
                    <a href="{{ route('staff.index') }}" class="btn btn-secondary ms-2">Reset</a>
                @endif
            </form>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mx-auto text-center" id="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>UNI ID</th>
                                <th>Nama</th>
                                <th>Posisi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($staff as $no => $item)
                                <tr>
                                    <td>{{ $no + $staff->firstItem() }}</td> {{-- Corrected for pagination index --}}
                                    <td>{{ $item->id_staf }}</td>
                                    <td>{{ $item->nama_staff }}</td>
                                    <td>{{ $item->posisi }}</td>
                                    <td>
                                        {{-- @can('admin-action') - uncomment this if you need to restrict actions --}}
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#editStaff{{ $item->id }}">Edit
                                        </button>
                                        <form action="{{ route('staff.destroy', $item->id) }}" method="POST"
                                            style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                                        </form>
                                        {{-- @endcan --}}
                                    </td>
                                    {{-- Include the edit modal for each item --}}
                                    @include('modal.edit-staff', ['staff' => $item]) {{-- Pass $item to modal --}}
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data staff ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{ $staff->links() }} {{-- Laravel Pagination Links --}}
                </div>
            </div>
        </div>
    </div>
@endsection

