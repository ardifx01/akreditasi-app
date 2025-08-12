@extends('layouts.app')

@section('content')
@section('title', 'Cek Kehadiran Per Bulan')
<div class="container">
    <h4>Cek Kehadiran Per Bulan</h4>

    <form method="GET" action="{{ route('kunjungan.cekKehadiran') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
            <label for="cardnumber" class="form-label">Nomor Kartu Anggota (Cardnumber)</label>
            <input type="text" name="cardnumber" id="cardnumber" class="form-control"
                value="{{ old('cardnumber', $cardnumber ?? '') }}" placeholder="Masukkan Nomor Kartu Anggota...">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Lihat</button>
        </div>
        @if ($fullBorrowerDetails && $dataKunjungan->isNotEmpty())
            <div class="col-md-3">
                <a href="{{ route('kunjungan.export-pdf', ['cardnumber' => $fullBorrowerDetails->cardnumber]) }}"
                    class="btn btn-danger w-100">Export ke PDF</a>
            </div>
            <div class="col-md-3">
                <button type="button" id="downloadExportDataButton" class="btn btn-success w-100">Export ke
                    CSV</button>
            </div>
        @endif
    </form>

    @if ($pesan)
        <div class="alert alert-info text-center" role="alert">
            {{ $pesan }}
        </div>
    @endif

    @if ($fullBorrowerDetails && $dataKunjungan->isNotEmpty())
        <div class="card mb-4">
            <div class="card-body">
                <button id="saveChart" class="btn btn-sm btn-success">Save Pdf</button>
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
                                <th>Bulan Tahun</th>
                                <th>Jumlah Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataKunjungan as $row)
                                <tr>
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
        @if ($fullBorrowerDetails && $dataKunjungan->isNotEmpty())
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

            document.getElementById("saveChart").addEventListener("click", function() {
                const newCanvas = document.createElement("canvas");
                newCanvas.width = chartCanvas.width;
                newCanvas.height = chartCanvas.height;

                const context = newCanvas.getContext("2d");
                context.fillStyle = "#ffffff";
                context.fillRect(0, 0, newCanvas.width, newCanvas.height);
                context.drawImage(chartCanvas, 0, 0);
                const imageURL = newCanvas.toDataURL("image/png");

                const downloadLink = document.createElement("a");
                downloadLink.href = imageURL;
                downloadLink.download =
                    "chart_kunjungan_anggota_{{ Str::slug($fullBorrowerDetails->cardnumber ?? 'unknown') }}.png";
                downloadLink.click();
            });

            const downloadExportDataButton = document.getElementById("downloadExportDataButton");
            if (downloadExportDataButton) {
                downloadExportDataButton.addEventListener("click", async function() {
                    const cardnumber = document.getElementById('cardnumber').value;

                    if (!cardnumber) {
                        alert("Mohon masukkan Nomor Kartu Anggota terlebih dahulu.");
                        return;
                    }

                    try {
                        const response = await fetch(
                            `{{ route('kunjungan.get_export_data') }}?cardnumber=${cardnumber}`
                        );
                        const result = await response.json();

                        if (response.ok) {
                            if (result.data.length === 0) {
                                alert("Tidak ada data untuk diekspor.");
                                return;
                            }

                            let csv = [];
                            const delimiter = ';';

                            const headers = ['Bulan Tahun',
                                'Jumlah Kunjungan'
                            ];
                            csv.push(headers.join(delimiter));


                            result.data.forEach(row => {
                                const rowData = [
                                    `"${row.bulan_tahun.replace(/"/g, '""')}"`,
                                    row.jumlah_kunjungan
                                ];
                                csv.push(rowData.join(delimiter));
                            });

                            const csvString = csv.join('\n');
                            const BOM =
                                "\uFEFF";
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
                });
            }
        @endif
    });
</script>
@endsection
