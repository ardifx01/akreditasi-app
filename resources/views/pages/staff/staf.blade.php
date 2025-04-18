@extends('layouts.app')

@section('title', 'STAFF')

@section('content')
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>UNI ID</th>
            <th>Nama</th>
            {{-- <th>Aksi</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach ($staff as $no => $item)
            <tr>
                <td>{{ $no + 1 }}</td>
                <td>{{ $item->id_staf }}</td>
                <td>{{ $item->nama_staff }}</td>
                {{-- <td>
                    <button type="click" class="btn btn-primary edit" data-id="{{ $item->id }}">Edit</button>
                    <a href="{{ url($item->id.'/delete') }}">
                        <button class="btn btn-danger">Delete</button>
                    </a>
                </td> --}}
            </tr>
        @endforeach
    </tbody>
</table>
@endsection