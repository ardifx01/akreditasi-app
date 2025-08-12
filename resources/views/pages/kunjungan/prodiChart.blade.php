@extends('layouts.app')

@section('content')
@section('title', 'Grafik Kunjungan Prodi')
<div class="container">
    <h4>Grafik Kunjungan Prodi @if ($selectedProdi && $selectedTahunAwal && $selectedTahunAkhir && $namaProdi)
            -- {{ $namaProdi }}
        @endif
    </h4>

    {{-- Form Filter --}}
    <form method="GET" action="{{ route('kunjungan.prodiChart') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-4">
            <label for="prodi" class="form-label">Pilih Prodi</label>
            <select name="prodi" id="prodi" class="form-select">
                <option value="">-- Pilih Program Studi --</option>
                <option value="all" {{ $selectedProdi == 'all' ? 'selected' : '' }}>-- Semua Prodi --</option>
                @foreach ($listProdi as $kode => $nama)
                    <option value="{{ $kode }}" {{ $selectedProdi == $kode ? 'selected' : '' }}>
                        ({{ $kode }})
                        -- {{ $nama }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="tahun_awal" class="form-label">Tahun Awal</label>
            <input type="number" name="tahun_awal" id="tahun_awal" class="form-control"
                value="{{ $selectedTahunAwal }}">
        </div>
        <div class="col-md-2">
            <label for="tahun_akhir" class="form-label">Tahun Akhir</label>
            <input type="number" name="tahun_akhir" id="tahun_akhir" class="form-control"
                value="{{ $selectedTahunAkhir }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    @if ($selectedProdi && $selectedTahunAwal && $selectedTahunAkhir && $data->isNotEmpty())
        {{-- Chart --}}
        <div class="card mb-4">
            <div class="card-body">
                <button id="saveChart" class="btn btn-sm btn-success">Save Pdf</button>
                <canvas id="chartKunjungan" height="100"></canvas>
            </div>
        </div>

        {{-- Table for data --}}
        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="kunjunganProdiTable">
                        <thead>
                            <tr>
                                <th>Bulan Tahun</th>
                                <th>Prodi</th>
                                <th>Jumlah Kunjungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $row)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Ym', $row->tahun_bulan)->format('M Y') }}
                                    </td>
                                    <td>{{ $row->nama_prodi }}</td>
                                    <td>{{ $row->jumlah_kunjungan }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Tambahkan link paginasi di sini --}}
                <div class="d-flex justify-content-center mt-3 row">
                    {{ $data->links() }}
                    {{-- PERBAIKAN DI SINI: Gunakan variabel totalKeseluruhanKunjungan --}}
                    <p class="mt-3">Total Keseluruhan Kunjungan: {{ $totalKeseluruhanKunjungan }}</p>
                </div>
            </div>
        </div>
    @elseif ($selectedProdi && $selectedTahunAwal && $selectedTahunAkhir && $data->isEmpty())
        <div class="alert alert-info text-center" role="alert">
            Tidak ada data kunjungan yang ditemukan untuk program studi {{ $namaProdi }}
            pada rentang tahun {{ $selectedTahunAwal }} hingga {{ $selectedTahunAkhir }}.
        </div>
    @else
        <div class="alert alert-info text-center" role="alert">
            Silakan pilih program studi dan rentang tahun untuk menampilkan laporan kunjungan.
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if ($selectedProdi && $selectedTahunAwal && $selectedTahunAkhir && $data->isNotEmpty())
            const chartCanvas = document.getElementById('chartKunjungan');
            const chart = chartCanvas.getContext('2d');

            const dataChart = new Chart(chart, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(
                        $data->pluck('tahun_bulan')->map(fn($v) => \Carbon\Carbon::createFromFormat('Ym', $v)->format('M Y')),
                    ) !!},
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
                downloadLink.download = "chart_kunjungan_{{ Str::slug($namaProdi) }}.png";
                downloadLink.click();
            });
        @endif
    });
</script>
@endsection
