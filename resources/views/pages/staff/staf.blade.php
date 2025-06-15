@extends('layouts.app')

@section('title', 'STAFF')

@section('content')
@can('admin-action')
<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#addStaffModal">
    <i class="fas fa-plus me-2"></i> Tambah Data Staff
</button>

@include('modal.create-staff')

@endcan

<div class="mt-4">
    <table class="table table-striped table-hover">
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
            @foreach ($staff as $no => $item)
                <tr>
                    <td>{{ $no + 1 }}</td>
                    <td>{{ $item->id_staf }}</td>
                    <td>{{ $item->nama_staff }}</td>
                    <td>{{ $item->posisi }}</td>
                    @can('admin-action')
                    <td>
                        {{-- <a href="{{ route('staff.edit', $item->id) }}" class="btn btn-primary">Edit</a> --}}
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editStaff{{ $item->id }}">Edit
                        </button>
                        <form action="{{ route('staff.destroy', $item->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                        </form>
                    </td>
                    @include('modal.edit-staff')
                    @endcan
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $staff->links() }}
</div>
@endsection