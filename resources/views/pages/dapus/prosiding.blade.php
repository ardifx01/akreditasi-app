@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Statistik Koleksi Prosiding - {{ $namaProdi }}</h4>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('koleksi.prosiding') }}" class="row g-3 mb-4">
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
                <button id="downloadExcel" class="btn btn-warning mt-3 mb-2">Save Tabel (Excel)</button>
                <table class="table table-bordered table-hover table-striped" id="myTable">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kelas</th>
                            <th>Penerbit</th>
                            <th>Tahun Terbit</th>
                            <th>Nomor</th>
                            <th>Issue</th>
                            <th>Eksemplar</th>
                            <th>Lokasi</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <td>{{ $row->Judul }}</td>
                                <td>{{ $row->Kelas }}</td>
                                <td>{{ $row->Penerbit }}</td>
                                <td>{{ $row->TahunTerbit }}</td>
                                <td>{{ $row->Nomor }}</td>
                                <td>{{ $row->Issue }}</td>
                                <td>{{ $row->Eksemplar }}</td>
                                <td>{{ $row->Lokasi }}</td>
                                <td><a href="{{ $row->Link }}">{{ $row->Link }}</a></td>
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
<script>
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
        const fileName = "prosiding.csv";

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
