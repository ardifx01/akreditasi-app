@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Laporan Kunjungan Prodi -- {{ $namaProdi }} --</h4>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('kunjungan.prodiChart') }}" class="row g-3 mb-4">
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
            <input type="number" name="tahun_awal" id="tahun_awal" class="form-control" value="{{ request('tahun_awal', now()->year - 5) }}">
        </div>
        <div class="col-md-2">
            <label for="tahun_akhir" class="form-label">Tahun Akhir</label>
            <input type="number" name="tahun_akhir" id="tahun_akhir" class="form-control" value="{{ request('tahun_akhir', now()->year) }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    {{-- Chart --}}
    <div class="card mb-4">
        <div class="card-body">
            <button id="saveChart" class="btn btn-sm btn-success">Save Pdf</button>
            <canvas id="chartKunjungan" height="100"></canvas>
        </div>
    </div>
    <div class="mt-3">
        {{ $data->links() }}
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chart = document.getElementById('chartKunjungan').getContext('2d');

    const dataChart = new Chart(chart, {
        type: 'bar',
        data: {
            labels: {!! json_encode($data->pluck('tahun_bulan')->map(fn($v) => \Carbon\Carbon::createFromFormat('Ym', $v)->format('M Y'))) !!},
            datasets: [{
                label: 'Jumlah Kunjungan {{ $namaProdi }}',
                data: {!! json_encode($data->pluck('jumlah_kunjungan')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.7,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<script>
    document.getElementById("saveChart").addEventListener("click", function () {
        const chartCanvas = document.getElementById("chartKunjungan");
        // Buat canvas baru
        const newCanvas = document.createElement("canvas");
        newCanvas.width = chartCanvas.width;
        newCanvas.height = chartCanvas.height;

        const context = newCanvas.getContext("2d");
        // Tambahkan latar belakang putih
        context.fillStyle = "#ffffff"; // ubah sesuai kebutuhan
        context.fillRect(0, 0, newCanvas.width, newCanvas.height);
        // Gambar chart asli di atas background
        context.drawImage(chartCanvas, 0, 0);
        // Konversi ke URL dan unduh
        const imageURL = newCanvas.toDataURL("image/png");

        const downloadLink = document.createElement("a");
        downloadLink.href = imageURL;
        downloadLink.download = "chart_kunjungan.png";
        downloadLink.click();
    });
</script>
@endsection

