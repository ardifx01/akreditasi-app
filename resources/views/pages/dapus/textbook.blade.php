@extends('layouts.app')
@section('title', 'Statistik Koleksi Text Book')

@section('content')
    <div class="container">
        <h4>Statistik Koleksi Text Book @if ($prodi)
                - {{ $namaProdi }}
            @endif
        </h4>
        <form method="GET" action="{{ route('koleksi.textbook') }}" class="row g-3 mb-4 align-items-end"
            id="filterFormTextbook">
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
                    Daftar Koleksi Text Book @if ($namaProdi)
                        ({{ $namaProdi }})
                    @endif
                    @if ($tahunTerakhir !== 'all')
                        - {{ $tahunTerakhir }} Tahun Terakhir
                    @endif
                    <button type="submit" form="filterFormTextbook" name="export_csv" value="1"
                        class="btn btn-success btn-sm">Export CSV</button>
                </div>
            @endif
            <div class="card-body">
                @if ($prodi && $data->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="myTableTextbook">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Pengarang</th>
                                    <th>Penerbit</th>
                                    <th>Kota Terbit</th>
                                    <th>Tahun Terbit</th>
                                    <th>Eksemplar</th>
                                    <th>Lokasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data as $row)
                                    <tr>
                                        <td>{{ $row->Judul }}</td>
                                        <td>{{ $row->Pengarang }}</td>
                                        <td>{{ $row->Penerbit }}</td>
                                        <td>{{ $row->Kota_Terbit }}</td>
                                        <td>{{ $row->Tahun_Terbit }}</td>
                                        <td>{{ $row->Eksemplar }}</td>
                                        <td>{{ $row->Lokasi }}</td>
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
                        Silakan pilih program studi dan filter tahun untuk menampilkan data Text Book.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
        });
    </script>
@endsection
