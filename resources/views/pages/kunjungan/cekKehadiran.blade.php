@extends('layouts.app')

@section('content')
@section('title', 'Cek Kehadiran Per Bulan')
<div class="container">
    <h4>Cek Kehadiran Per Bulan</h4>

    <form method="GET" action="{{ route('kunjungan.cekKehadiran') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-6">
            <label for="cardnumber" class="form-label">Nomor Kartu Anggota (Cardnumber)</label>
            <input type="text" name="cardnumber" id="cardnumber" class="form-control"
                value="{{ old('cardnumber', $cardnumber ?? '') }}" placeholder="Masukkan Nomor Kartu Anggota...">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Lihat Laporan</button>
        </div>
    </form>

    @if ($pesan)
        <div class="alert alert-info text-center" role="alert">
            {{ $pesan }}
        </div>
    @endif
    @if ($borrowerInfo && $dataKunjungan->isNotEmpty())


        {{-- Chart Kunjungan --}}
        <div class="card mb-4">
            <div class="card-body">
                <button id="saveChart" class="btn btn-sm btn-success">Save Pdf</button>
                <canvas id="chartKunjungan" height="100"></canvas>
            </div>
        </div>

        {{-- Tabel Kunjungan --}}
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
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if ($borrowerInfo && $dataKunjungan->isNotEmpty())
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
                        tension: 0.7,
                        fill: false
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

            // Save Chart (PDF/PNG) logic
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
                    "chart_kunjungan_mahasiswa_{{ Str::slug($borrowerInfo->cardnumber ?? 'unknown') }}.png";
                downloadLink.click();
            });
        @endif
    });
</script>
@endsection
