@extends('layouts.app')
@section('title', 'Statistik Koleksi Periodikal')

@section('content')
    <div class="container">
        <h4>Statistik Koleksi Periodikal @if ($prodi)
                - {{ $namaProdi }}
            @endif
        </h4>
        <form method="GET" action="{{ route('koleksi.periodikal') }}" class="row g-3 mb-4 align-items-end"
            id="filterFormPeriodikal">
            <div class="col-md-4">
                <label for="prodi" class="form-label">Pilih Prodi</label>
                <select name="prodi" id="prodi" class="form-select">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($listprodi as $itemProdi)
                        <option value="{{ $itemProdi->kode }}" {{ $prodi == $itemProdi->kode ? 'selected' : '' }}>
                            ({{ $itemProdi->kode }})
                            -- {{ $itemProdi->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="tahun" class="form-label">Tahun Terbit</label>
                <select name="tahun" id="tahun" class="form-select">
                    <option value="all" {{ $tahunTerakhir == 'all' ? 'selected' : '' }}>Semua Tahun</option>
                    @for ($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ $tahunTerakhir == $i ? 'selected' : '' }}>
                            {{ $i }} Tahun Terakhir
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
            </div>
        </form>
        <div class="card">
            @if ($prodi && $dataExists)
                <div class="card-header d-flex justify-content-between align-items-center">
                    Daftar Koleksi Periodikal @if ($namaProdi)
                        ({{ $namaProdi }})
                    @endif
                    @if ($tahunTerakhir !== 'all')
                        - {{ $tahunTerakhir }} Tahun Terakhir
                    @endif
                    <button type="submit" form="filterFormPeriodikal" name="export_csv" value="1"
                        class="btn btn-success btn-sm">Export CSV</button>
                </div>
            @endif
            <div class="card-body">
                @if ($prodi && $data->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="myTablePeriodikal">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Judul</th>
                                    <th>Nomor</th>
                                    <th>Kelas</th>
                                    <th>Lokasi</th>
                                    <th>Issue</th>
                                    <th>Eksemplar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                    <tr>
                                        <td>{{ $row->Jenis }}</td>
                                        <td>{{ $row->Judul }}</td>
                                        <td>{{ $row->Nomor }}</td>
                                        <td>{{ $row->Kelas }}</td>
                                        <td>{{ $row->Lokasi }}</td>
                                        <td>{{ $row->Issue }}</td>
                                        <td>{{ $row->Eksemplar }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Data tidak ditemukan untuk prodi ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $data->links() }}
                    </div>
                @elseif ($prodi && $data->isEmpty())
                    <div class="alert alert-info text-center" role="alert">
                        Data tidak ditemukan untuk program studi ini @if ($tahunTerakhir !== 'all')
                            dalam {{ $tahunTerakhir }} tahun terakhir
                        @endif.
                    </div>
                @else
                    <div class="alert alert-info text-center" role="alert">
                        Silakan pilih program studi dan filter tahun untuk menampilkan data periodikal.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {});
    </script>
@endsection
