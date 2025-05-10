@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Laporan Kunjungan Prodi</h4>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('kunjungan.prodiTable') }}" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="prodi" class="form-label">Pilih Prodi</label>
            <select name="prodi" id="prodi" class="form-select">
                <option value="">-- Semua Prodi --</option>
                @foreach ($listProdi as $prodi)
                    <option value="{{ $prodi->kode}}">
                        ({{ $prodi->kode }}) -- {{ $prodi->nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="tahun_awal" class="form-label">Tahun Awal</label>
            <input type="number" name="tahun_awal" id="tahun_awal" class="form-control" value="{{ request('tahun_awal', now()->year - 2) }}">
        </div>
        <div class="col-md-2">
            <label for="tahun_akhir" class="form-label">Tahun Akhir</label>
            <input type="number" name="tahun_akhir" id="tahun_akhir" class="form-control" value="{{ request('tahun_akhir', now()->year) }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    {{-- Tabel --}}
    <button id="downloadTabel" class="btn btn-success mt-3 mb-2">Save Tabel</button>
    <div class="table-responsive" id="tabelLaporan">
        <table class="table table-bordered table-striped ">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tahun dan Bulan</th>
                    <th>Kode Prodi</th>
                    <th>Nama Prodi</th>
                    <th>Jumlah Kunjungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $index => $row)
                    <tr>
                        <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                        <td>{{ \Carbon\Carbon::createFromFormat('Ym', $row->tahun_bulan)->format('F Y') }}</td>
                        <td>{{ $row->kode_prodi }}</td>
                        <td>{{ $row->nama_prodi }}</td>
                        <td>{{ $row->jumlah_kunjungan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $data->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.getElementById("downloadTabel").addEventListener("click", function () {
        const element = document.getElementById("tabelLaporan");

        html2canvas(element, {
            backgroundColor: "#ffffff", // putih agar tidak transparan
            useCORS: true // penting jika ada elemen gambar/logo dari CDN
        }).then(canvas => {
            const link = document.createElement("a");
            link.download = "laporan_kunjungan.png";
            link.href = canvas.toDataURL("image/png");
            link.click();
        });
    });
</script>

@endsection

