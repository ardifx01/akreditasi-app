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
                @foreach ($listProdi as $itemProdi)
                    <option value="{{ $itemProdi->kode }}" {{ request('prodi') == $itemProdi->kode ? 'selected' : '' }}>
                        ({{ $itemProdi->kode }}) -- {{ $itemProdi->nama }}
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

    {{-- Tombol Download --}}
    <button id="downloadPng" class="btn btn-success mt-3 mb-2 me-2">Save Tabel (PNG)</button>
    <button id="downloadExcel" class="btn btn-warning mt-3 mb-2">Save Tabel (Excel)</button>

    <div class="table-responsive" id="tabelLaporan">
        <table class="table table-bordered table-striped" id="myTable">
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

{{-- Membutuhkan library html2canvas untuk fitur PNG --}}
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> {{-- Chart.js mungkin tidak diperlukan untuk fungsionalitas ini --}}
<script>
    // Script untuk Save Tabel (PNG)
    document.getElementById("downloadPng").addEventListener("click", function () {
        const element = document.getElementById("tabelLaporan");

        html2canvas(element, {
            backgroundColor: "#ffffff",
            useCORS: true
        }).then(canvas => {
            const link = document.createElement("a");
            link.download = "laporan_kunjungan.png";
            link.href = canvas.toDataURL("image/png");
            link.click();
        });
    });

    // Script untuk Save Tabel (Excel - CSV)
    document.getElementById("downloadExcel").addEventListener("click", function () {
        const table = document.getElementById("myTable");
        let csv = [];
        const delimiter = ';';

        // Ambil header tabel
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => {
            let text = th.innerText.trim();
            text = text.replace(/"/g, '""');
            if (text.includes(delimiter) || text.includes('"') || text.includes('\n')) {
                text = `"${text}"`;
            }
            return text;
        });
        csv.push(headers.join(delimiter));

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const rowData = Array.from(row.querySelectorAll('td')).map(td => {
                let text = td.innerText.trim();
                text = text.replace(/"/g, '""');
                if (text.includes(delimiter) || text.includes('"') || text.includes('\n')) {
                    text = `"${text}"`;
                }
                return text;
            });
            csv.push(rowData.join(delimiter));
        });

        const csvString = csv.join('\n');

        const BOM = "\uFEFF";
        const blob = new Blob([BOM + csvString], { type: 'text/csv;charset=utf-8;' }); 

        const link = document.createElement("a");
        const fileName = "laporan_kunjungan_prodi.csv";

        if (navigator.msSaveBlob) { 
            navigator.msSaveBlob(blob, fileName);
        } else {
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            link.click();
            URL.revokeObjectURL(link.href);
        }
    });
</script>
@endsection