@extends('layouts.app')
@section('title', 'Statistik Koleksi E-Book')

@section('content')
    <div class="container">
        <h4>Statistik Koleksi E-Book @if ($prodi)
                - {{ $namaProdi }}
            @endif
        </h4>

        <form method="GET" action="{{ route('koleksi.ebook') }}" class="row g-3 mb-4 align-items-end">
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
            <div class="card-body">
                @if ($prodi && $data->isNotEmpty())
                    <div class="table-responsive">
                        <button id="downloadExcel" class="btn btn-warning mt-3 mb-2">Save Tabel (Excel)</button>
                        <table class="table table-bordered table-hover table-striped" id="myTable">
                            <thead>
                                <tr>
                                    <th>Judul</th>
                                    <th>Pengarang</th>
                                    <th>Kota Terbit</th>
                                    <th>Penerbit</th>
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
                                        <td>{{ $row->Kota_Terbit }}</td>
                                        <td>{{ $row->Penerbit }}</td>
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
                        Silakan pilih program studi dan filter tahun untuk menampilkan data E-Book.
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- Script untuk Save Tabel (Excel - CSV) tetap di sini --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const downloadExcelButton = document.getElementById("downloadExcel");
            if (downloadExcelButton) {
                downloadExcelButton.addEventListener("click", function() {
                    const table = document.getElementById("myTable");
                    if (!table) {
                        console.error("Table 'myTable' not found.");
                        return;
                    }
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
                            if (text.includes(delimiter) || text.includes('"') || text
                                .includes('\n')) {
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
                    const fileName = "ebook_data.csv";

                    if (navigator.msSaveBlob) {
                        navigator.msSaveBlob(blob, fileName);
                    } else {
                        link.href = URL.createObjectURL(blob);
                        link.download = fileName;
                        link.click();
                        URL.revokeObjectURL(link.href);
                    }
                });
            }
        });
    </script>
@endsection
