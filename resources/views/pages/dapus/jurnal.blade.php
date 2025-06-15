@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Statistik Koleksi Jurnal - {{ $namaProdi }}</h4>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('koleksi.jurnal') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="prodi" class="form-label">Pilih Prodi</label>
            <select name="prodi" id="prodi" class="form-select">
                @foreach ($listprodi as $prodi)
                    <option value="{{ $prodi->kode }}">
                        ({{ $prodi->kode }}) -- {{ $prodi->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
        </div>
    </form>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Penerbit</th>
                            <th>Nomor</th>
                            <th>Kelas</th>
                            <th>Jenis</th>
                            <th>Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td>{{ $row->Judul }}</td>
                                <td>{{ $row->Penerbit }}</td>
                                <td>{{ $row->Nomor }}</td>
                                <td>{{ $row->Kelas }}</td>
                                <td>{{ $row->Jenis }}</td>
                                <td>{{ $row->Lokasi }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Data tidak ditemukan untuk prodi ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $data->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
