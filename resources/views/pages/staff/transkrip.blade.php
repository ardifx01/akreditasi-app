@extends('layouts.app')

@section('title', 'Transkrip')

@section('content')
    @can('admin-action')
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#transkripModal">
            <i class="fas fa-plus me-2"></i> Tambah Data Transkrip
        </button>

        @include('modal.create-transkrip')
    @endcan
    <div class="mt-4">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID Staff</th>
                    <th>Nama Transkrip</th>
                    <th>Nama File</th>
                    <th>Tahun</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transkrip as $no => $item)
                    @include('modal.view-pdf')
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>{{ $item->id_staf }}</td>
                        <td>{{ $item->judul_transkrip }}</td>
                        <td>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#view-pdf-{{ $item->id }}">
                                <i class="fas fa-eye"></i>
                                View Dokumen Transkrip
                            </button>
                        </td>
                        <td>{{ $item->tahun }}</td>
                        @can('admin-action')
                            <td>
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editTranskrip{{ $item->id }}">Edit
                                </button>
                                <form action="{{ route('transkrip.destroy', $item->id) }}" method="POST"
                                    style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                                </form>
                            </td>
                            @include('modal.edit-transkrip')
                        @endcan
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $transkrip->links() }}
    </div>
@endsection
