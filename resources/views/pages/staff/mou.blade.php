@extends('layouts.app')

@section('title', 'MoU')

@section('content')
    @can('admin-action')
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#mouModal">
            <i class="fas fa-plus me-2"></i> Tambah Data MoU
        </button>

        @include('modal.create-mou')
    @endcan
    <div class="mt-4">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Mou</th>
                    <th>Nama File</th>
                    <th>Tahun</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($mou as $no => $item)
                    @include('modal.view-pdf')
                    <tr>
                        <td>{{ $no + 1 }}</td>
                        <td>{{ $item->judul_mou }}</td>
                        <td>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#view-pdf-{{ $item->id }}">
                                <i class="fas fa-eye"></i>
                                View Dokumen MoU
                            </button>
                        </td>
                        {{-- <td>{{ $item->file_dokumen }}</td> --}}
                        <td>{{ $item->tahun }}</td>
                        @can('admin-action')
                            <td>
                                {{-- <a href="{{ route('staff.edit', $item->id) }}" class="btn btn-primary">Edit</a> --}}
                                <button class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editMou{{ $item->id }}">Edit
                                </button>
                                <form action="{{ route('mou.destroy', $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                                </form>
                            </td>
                            @include('modal.edit-mou')
                        @endcan
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $mou->links() }}
    </div>
@endsection
