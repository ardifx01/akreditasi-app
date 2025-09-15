@extends('layouts.app')

@section('content')
@section('title', 'Cek Kehadiran Per Bulan')
<div class="container">
    <h4>Cek Kehadiran Per Bulan</h4>

    <form method="GET" action="{{ route('kunjungan.cekKehadiran') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
            <label for="cardnumber" class="form-label">Nomor Kartu Anggota (Cardnumber)</label>
            <input type="text" name="cardnumber" id="cardnumber" class="form-control"
                value="{{ request('cardnumber') }}" />
        </div>
        <div class="col-md-2">
            <label for="tahun" class="form-label">Tahun</label>
            <select name="tahun" id="tahun" class="form-control">
                <option value="">Semua Tahun</option>
                @php
                    $currentYear = date('Y');
                    for ($year = $currentYear; $year >= 2020; $year--) {
                        echo "<option value='{$year}' " .
                            (request('tahun') == $year ? 'selected' : '') .
                            ">{$year}</option>";
                    }
                @endphp
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Lihat</button>
        </div>
        <div class="col-md-2">
            <button type="button" id="downloadPdfButton"
                class="btn btn-danger w-100 {{ !request('cardnumber') ? 'disabled' : '' }}">Export ke PDF</button>
        </div>
        <div class="col-md-3">
            <button type="button" id="downloadExportDataButton"
                class="btn btn-success w-100 {{ !request('cardnumber') ? 'disabled' : '' }}">Export ke CSV</button>
        </div>
    </form>

    @if (session('error'))
        <div class="alert alert-danger" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($pesan)
        <div class="alert alert-info text-center" role="alert">
            {{ $pesan }}
        </div>
    @endif

    @if (isset($fullBorrowerDetails) && $fullBorrowerDetails && $dataKunjungan->isNotEmpty())
        <div class="card mb-4">
            <div class="card-body">
                {{-- <button id="saveChart" class="btn btn-sm btn-success">Save Pdf</button> --}}
                <canvas id="chartKunjungan" height="100"></canvas>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                Informasi Anggota
            </div>
            <div class="card-body">
                <p><strong>Nomor Kartu Anggota:</strong> {{ $fullBorrowerDetails->cardnumber }}</p>
                <p><strong>Nama:</strong> {{ $fullBorrowerDetails->firstname }} {{ $fullBorrowerDetails->surname }}</p>
                <p><strong>Email:</strong> {{ $fullBorrowerDetails->email }}</p>
                <p><strong>Telepon:</strong> {{ $fullBorrowerDetails->phone }}</p>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="kunjunganTable">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Bulan Tahun</th>
                                <th>Jumlah Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataKunjungan as $row)
                                <tr>
                                    <td>{{ ($dataKunjungan->currentPage() - 1) * $dataKunjungan->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Ym', $row->tahun_bulan)->format('M Y') }}
                                    </td>
                                    <td>{{ $row->jumlah_kunjungan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-3 row">
                        {{ $dataKunjungan->links() }}
                        <p class="mt-3">Total Keseluruhan Kunjungan: {{ $dataKunjungan->sum('jumlah_kunjungan') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Logika untuk tombol Export ke PDF
        const downloadPdfButton = document.getElementById("downloadPdfButton");
        if (downloadPdfButton) {
            downloadPdfButton.addEventListener("click", function() {
                const cardnumber = document.getElementById('cardnumber').value;
                const tahun = document.getElementById('tahun').value;
                if (cardnumber) {
                    window.open(
                        `{{ route('kunjungan.export-pdf') }}?cardnumber=${cardnumber}&tahun=${tahun}`,
                        '_blank'
                    );
                } else {
                    alert("Mohon masukkan Nomor Kartu Anggota terlebih dahulu.");
                }
            });
        }

        const downloadExportDataButton = document.getElementById("downloadExportDataButton");
        if (downloadExportDataButton) {
            downloadExportDataButton.addEventListener("click", async function() {
                const cardnumber = document.getElementById('cardnumber').value;
                const tahun = document.getElementById('tahun').value; // Ambil nilai tahun dari form

                if (!cardnumber) {
                    alert("Mohon masukkan Nomor Kartu Anggota terlebih dahulu.");
                    return;
                }

                try {
                    const response = await fetch(
                        `{{ route('kunjungan.get_export_data') }}?cardnumber=${cardnumber}&tahun=${tahun}`
                    );
                    const result = await response.json();
                    if (response.ok) {
                        if (result.data.length === 0) {
                            alert("Tidak ada data untuk diekspor.");
                            return;
                        }

                        let csv = [];
                        const delimiter = ';';
                        const BOM = "\uFEFF";

                        const headers = ['Bulan Tahun', 'Jumlah Kunjungan'];
                        csv.push(headers.join(delimiter));

                        result.data.forEach(row => {
                            const rowData = [
                                `"${row.bulan_tahun.replace(/"/g, '""')}"`,
                                row.jumlah_kunjungan
                            ];
                            csv.push(rowData.join(delimiter));
                        });

                        const csvString = csv.join('\n');
                        const blob = new Blob([BOM + csvString], {
                            type: 'text/csv;charset=utf-8;'
                        });

                        const link = document.createElement("a");
                        const fileName =
                            `laporan_kehadiran_${result.cardnumber}_${(result.borrower_name || 'unknown').replace(/\s+/g, '_').toLowerCase()}_${new Date().toISOString().slice(0,10).replace(/-/g,'')}.csv`;

                        if (navigator.msSaveBlob) {
                            navigator.msSaveBlob(blob, fileName);
                        } else {
                            link.href = URL.createObjectURL(blob);
                            link.download = fileName;
                            document.body.appendChild(link);
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
            });
        }

        // Logika Chart.js
        @if (isset($fullBorrowerDetails) && $fullBorrowerDetails && $dataKunjungan->isNotEmpty())
            const chartCanvas = document.getElementById('chartKunjungan');
            const chart = chartCanvas.getContext('2d');

            const dataChart = new Chart(chart, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(
                        $dataKunjungan->pluck('tahun_bulan')->map(fn($v) => \Carbon\Carbon::createFromFormat('Ym', $v)->format('M Y')),
                    ) !!},
                    datasets: [{
                        label: 'Jumlah Kunjungan per Bulan',
                        data: {!! json_encode($dataKunjungan->pluck('jumlah_kunjungan')) !!},
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgb(75, 192, 192)',
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        @endif
    });
</script>
@endsection
