@extends('layouts.app')

@section('title', 'Pelatihan')

@section('content')
    @can('admin-action')
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#pelatihanModal">
            <i class="fas fa-plus me-2"></i> Tambah Data Pelatihan
        </button>

        @include('modal.create-pelatihan')
    @endcan
  <div class="mt-4">
      <table class="table table-striped table-hover">
          <thead>
              <tr>
                  <th>No</th>
                  <th>ID Staff</th>
                  <th>Nama Pelatihan</th>
                  <th>Nama File</th>
                  <th>Tahun</th>
                  <th>Aksi</th>
              </tr>
          </thead>
          <tbody>
              @foreach ($pelatihan as $no => $item)
                  <tr>
                      <td>{{ $no + 1 }}</td>
                      <td>{{ $item->id_staf }}</td>
                      <td>{{ $item->judul_pelatihan }}</td>
                      <td>{{ $item->file_dokumen }}</td>
                      <td>{{ $item->tahun }}</td>
                      @can('admin-action')
                      <td>
                          {{-- <a href="{{ route('staff.edit', $item->id) }}" class="btn btn-primary">Edit</a> --}}
                          <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editPelatihan{{ $item->id }}">Edit
                          </button>
                          <form action="{{ route('pelatihan.destroy', $item->id) }}" method="POST" style="display:inline;">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus?')">Delete</button>
                          </form>
                      </td>
                      @include('modal.edit-pelatihan')
                      @endcan
                  </tr>
              @endforeach
          </tbody>
      </table>
      {{ $pelatihan->links() }}
  </div>
@endsection
