@extends('layouts.app')

@section('content')
@section('title', 'Cek Histori Peminjaman')
<div class="container">
    <h4>Cek Histori Peminjaman</h4>

    <div class="card mb-4">
        <div class="card-header">
            Cari Berdasarkan Nomor Kartu
        </div>
        <div class="card-body">
            <form action="{{ route('peminjaman.check_history') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="cardnumber" class="form-label">Nomor Kartu Peminjam:</label>
                    <input type="text" name="cardnumber" id="cardnumber" class="form-control"
                        value="{{ $cardnumber ?? '' }}" placeholder="Masukkan nomor kartu">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
        </div>
    </div>

    @if ($errorMessage)
        <div class="alert alert-danger">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($borrower)
        <div class="card mb-4">
            <div class="card-header">
                Informasi Peminjam
            </div>
            <div class="card-body ">
                <p><strong>Nomor Kartu:</strong> {{ $borrower->cardnumber }}</p>
                <p><strong>Nama:</strong> {{ $borrower->firstname }} {{ $borrower->surname }}</p>
                <p><strong>Email:</strong> {{ $borrower->email }}</p>
                <p><strong>Telepon:</strong> {{ $borrower->phone }}</p>
            </div>
        </div>

        {{-- Histori Peminjaman --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Histori Peminjaman (Issue & Renew)
                @if ($borrowingHistory->isNotEmpty())
                    <button type="button" id="exportBorrowingHistory" class="btn btn-sm btn-success"><i
                            class="fas fa-file-csv"></i> Export CSV</button>
                @endif
            </div>
            <div class="card-body">
                @if ($borrowingHistory->isEmpty())
                    <div class="alert alert-info">Belum ada histori peminjaman atau perpanjangan untuk peminjam ini.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="borrowingTable">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Tipe</th>
                                    <th>Barcode Buku</th>
                                    <th>Judul Buku</th>
                                    <th>Pengarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($borrowingHistory as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->datetime)->format('d M Y H:i:s') }}</td>
                                        <td>{{ ucfirst($history->type) }}</td>
                                        <td>{{ $history->barcode }}</td>
                                        <td>{{ $history->title }}</td>
                                        <td>{{ $history->author }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end">
                            {{ $borrowingHistory->links() }}
                        </div>
                    </div>
                @endif
            </div>

        </div>

        {{-- Histori Pengembalian --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                Histori Pengembalian (Return)
                @if ($returnHistory->isNotEmpty())
                    <button type="button" id="exportReturnHistory" class="btn btn-sm btn-success"><i
                            class="fas fa-file-csv"></i> Export CSV</button>
                @endif
            </div>
            <div class="card-body">
                @if ($returnHistory->isEmpty())
                    <div class="alert alert-info">Belum ada histori pengembalian untuk peminjam ini.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="returnTable">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Tipe</th>
                                    <th>Barcode Buku</th>
                                    <th>Judul Buku</th>
                                    <th>Pengarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($returnHistory as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->datetime)->format('d M Y H:i:s') }}</td>
                                        <td>{{ ucfirst($history->type) }}</td>
                                        <td>{{ $history->barcode }}</td>
                                        <td>{{ $history->title }}</td>
                                        <td>{{ $history->author }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end">
                            {{ $returnHistory->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @elseif ($cardnumber && !$errorMessage)
        <div class="alert alert-warning">
            Nomor kartu peminjam "<strong>{{ $cardnumber }}</strong>" tidak ditemukan.
        </div>
    @endif
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        async function downloadCsv(url, defaultFileName, headers) {
            const cardnumber = document.getElementById('cardnumber').value;

            if (!cardnumber) {
                alert("Mohon masukkan Nomor Kartu Peminjam terlebih dahulu.");
                return;
            }

            try {
                const response = await fetch(`${url}?cardnumber=${cardnumber}`);
                const result = await response.json();

                if (response.ok) {
                    if (result.data.length === 0) {
                        alert(`Tidak ada data untuk diekspor untuk ${result.type} ini.`);
                        return;
                    }

                    let csv = [];
                    const delimiter = ';';

                    csv.push(headers.join(delimiter));

                    result.data.forEach(row => {
                        const rowData = headers.map(header => {
                            const key = header.toLowerCase().replace(/ & /g, '_').replace(
                                / /g, '_'); // Konversi header ke key yang sesuai
                            let text = row[key] !== undefined ? String(row[key]) :
                                ''; // Pastikan tidak undefined
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
                    const fileName =
                        `${defaultFileName}_${result.cardnumber}_${(result.borrower_name || 'unknown').replace(/\s+/g, '_').toLowerCase()}_${new Date().toISOString().slice(0,10).replace(/-/g,'')}.csv`;

                    if (navigator.msSaveBlob) {
                        navigator.msSaveBlob(blob, fileName);
                    } else {
                        link.href = URL.createObjectURL(blob);
                        link.download = fileName;
                        document.body.appendChild(
                            link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(link.href);
                    }
                } else {
                    alert(result.error || "Terjadi kesalahan saat mengambil data export.");
                }
            } catch (error) {
                console.error('Error fetching export data:', error);
                alert("Terjadi kesalahan teknis saat mencoba mengekspor data.");
            }
        }

        const exportBorrowingHistoryButton = document.getElementById("exportBorrowingHistory");
        if (exportBorrowingHistoryButton) {
            exportBorrowingHistoryButton.addEventListener("click", function() {
                const headers = ['Tanggal & Waktu', 'Tipe', 'Barcode Buku', 'Judul Buku', 'Pengarang'];
                downloadCsv(`{{ route('peminjaman.get_borrowing_export_data') }}`,
                    'histori_peminjaman', headers);
            });
        }

        // Event Listener untuk Export Histori Pengembalian
        const exportReturnHistoryButton = document.getElementById("exportReturnHistory");
        if (exportReturnHistoryButton) {
            exportReturnHistoryButton.addEventListener("click", function() {
                const headers = ['Tanggal & Waktu', 'Tipe', 'Barcode Buku', 'Judul Buku', 'Pengarang'];
                downloadCsv(`{{ route('peminjaman.get_return_export_data') }}`, 'histori_pengembalian',
                    headers);
            });
        }
    });
</script>
@endsection
