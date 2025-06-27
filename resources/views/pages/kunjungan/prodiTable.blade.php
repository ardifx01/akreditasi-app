@extends('layouts.app')

@section('title', 'Laporan Kunjungan Prodi')
@section('content')
    <div class="container">
        <h4>Laporan Kunjungan</h4>

        {{-- Form Filter --}}
        <form method="GET" action="{{ route('kunjungan.prodiTable') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="prodi" class="form-label">Pilih Prodi/Tipe User</label> {{-- Label diubah --}}
                <select name="prodi" id="prodi" class="form-select">
                    <option value="">-- Semua Prodi & Dosen/Tendik --</option> {{-- Opsi diubah --}}
                    <option value="DOSEN_TENDIK" {{ request('prodi') == 'DOSEN_TENDIK' ? 'selected' : '' }}>
                        -- Dosen / Tenaga Kependidikan --
                    </option>
                    {{-- Opsi prodi lainnya dari database --}}
                    @foreach ($listProdi as $kode => $nama)
                        <option value="{{ $kode }}" {{ request('prodi') == $kode ? 'selected' : '' }}>
                            ({{ $kode }})
                            -- {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                    value="{{ request('tanggal_awal', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
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
                        <th>Kode Identifikasi</th> {{-- Nama kolom diubah untuk lebih generik --}}
                        <th>Tipe User / Nama Prodi</th> {{-- Nama kolom diubah untuk lebih generik --}}
                        <th>Jumlah Kunjungan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $index => $row)
                        <tr>
                            <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal_kunjungan)->format('d F Y') }}</td>
                            <td>{{ $row->kode_prodi }}</td> {{-- Ini akan menampilkan DOSEN_TENDIK atau kode prodi --}}
                            <td>{{ $row->nama_prodi }}</td> {{-- Ini akan menampilkan "Dosen / Tenaga Kependidikan" atau nama prodi --}}
                            <td>{{ $row->jumlah_kunjungan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data kunjungan ditemukan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $data->links() }}
            <p class="mt-3">Total Kunjungan: {{ $data->sum('jumlah_kunjungan') }}</p>
        </div>
    </div>

    {{-- Script untuk Save Tabel (PNG) dan (Excel) --}}
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        
        document.getElementById("downloadPng").addEventListener("click", function() {
            const element = document.getElementById("tabelLaporan");
            html2canvas(element, {
                backgroundColor: "#ffffff",
                useCORS: true
            }).then(canvas => {
                const link = document.createElement("a");
                link.download = "laporan_kunjungan_prodi_dosen.png";
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        });

        document.getElementById("downloadExcel").addEventListener("click", function() {
            const table = document.getElementById("myTable");
            let csv = [];
            const delimiter = ';';

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
            const fileName = "laporan_kunjungan_prodi_dosen.csv";

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
