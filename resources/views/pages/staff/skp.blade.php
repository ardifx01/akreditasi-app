@extends('layouts.app')

@section('title', 'SKP')

@section('content')
    @can('admin-action')
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#skpModal">
            <i class="fas fa-plus me-2"></i> Tambah Data SKP
        </button>

        @include('modal.create-skp')
    @endcan
  <div class="mt-4">
      <table class="table table-striped table-hover">
          <thead>
              <tr>
                  <th>No</th>
                  <th>ID Staff</th>
                  <th>Nama Skp</th>
                  <th>Nama File</th>
                  <th>Tahun</th>
                  <th>Aksi</th>
              </tr>
          </thead>
          <tbody>
              @foreach ($skp as $no => $item)
              @include('modal.view-pdf')
                  <tr>
                      <td>{{ $no + 1 }}</td>
                      <td>{{ $item->id_staf }}</td>
                      <td>{{ $item->judul_skp }}</td>
                      <td>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#view-pdf-{{ $item->id }}">
                            <i class="fas fa-eye"></i>
                            View Dokumen SKP
                        </button>
                    </td>
                      <td>{{ $item->tahun }}</td>
                      @can('admin-action')
                      <td>
                          {{-- <a href="{{ route('staff.edit', $item->id) }}" class="btn btn-primary">Edit</a> --}}
                          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editSkp{{ $item->id }}">Edit
                          </button>
                          <form action="{{ route('skp.destroy', $item->id) }}" method="POST" style="display:inline;">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                          </form>
                      </td>
                      @include('modal.edit-skp')
                      @endcan
                  </tr>
              @endforeach
          </tbody>
      </table>
      {{ $skp->links() }}
  </div>
@endsection
