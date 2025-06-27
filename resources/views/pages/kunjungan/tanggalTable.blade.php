@extends('layouts.app')

@section('title', 'Laporan Kunjungan Harian') {{-- Judul diubah --}}
@section('content')
    <div class="container">
        <h4>Laporan Kunjungan Harian</h4> {{-- Judul diubah --}}

        {{-- Form Filter Tanggal Saja --}}
        <form method="GET" action="{{ route('kunjungan.tanggalTable') }}" class="row g-3 mb-4">
            <div class="col-md-5">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                    value="{{ request('tanggal_awal', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-5">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                    value="{{ request('tanggal_akhir', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')) }}">
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
                        <th>Tanggal Kunjungan</th>
                        <th>Total Kunjungan Harian</th> {{-- Nama kolom diubah --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $index => $row)
                        <tr>
                            <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal_kunjungan)->format('d F Y') }}</td>
                            <td>{{ $row->jumlah_kunjungan_harian }}</td> {{-- Akses alias baru --}}
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data kunjungan ditemukan untuk filter ini.</td>
                            {{-- colspan diubah --}}
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $data->links() }}
            <p class="mt-3">Total Keseluruhan Kunjungan: {{ $data->sum('jumlah_kunjungan_harian') }}</p>
            {{-- Sum diubah --}}
        </div>
    </div>

    {{-- Script untuk Save Tabel (PNG) dan (Excel) --}}
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        // Script untuk Save Tabel (PNG)
        document.getElementById("downloadPng").addEventListener("click", function() {
            const element = document.getElementById("tabelLaporan");
            html2canvas(element, {
                backgroundColor: "#ffffff",
                useCORS: true
            }).then(canvas => {
                const link = document.createElement("a");
                link.download = "laporan_kunjungan_harian.png"; // Nama file diubah
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        });

        // Script untuk Save Tabel (Excel - CSV)
        document.getElementById("downloadExcel").addEventListener("click", function() {
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
            const blob = new Blob([BOM + csvString], {
                type: 'text/csv;charset=utf-8;'
            });

            const link = document.createElement("a");
            const fileName = "laporan_kunjungan_harian.csv"; // Nama file diubah

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
