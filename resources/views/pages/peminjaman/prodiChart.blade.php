@extends('layouts.app') {{-- Sesuaikan dengan layout aplikasi Anda --}}

@section('content')
@section('title', 'Statistik Peminjaman per Program Studi')
<div class="container">
    <h4>Statistik Peminjaman per Program Studi</h4>

    {{-- Form Filter --}}
    <form action="{{ route('peminjaman.peminjaman_prodi_chart') }}" method="GET" class="row g-3 mb-4 align-items-end">

        <div class="col-md-3">
            <label for="selected_year" class="form-label">Pilih Tahun:</label>
            <select name="selected_year" id="selected_year" class="form-select">
                @php
                    $currentYear = \Carbon\Carbon::now()->year;
                    $startYear = $currentYear - 5; // Tampilkan 5 tahun ke belakang
                    $endYear = $currentYear;
                @endphp
                @for ($year = $startYear; $year <= $endYear; $year++)
                    <option value="{{ $year }}"
                        {{ ($selectedYear ?? $currentYear) == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-4">
            <label for="selected_prodi" class="form-label">Pilih Program Studi:</label><br>
            <small class="form-text text-muted">Tekan Ctrl/Cmd untuk memilih lebih dari satu.</small>
            <select name="selected_prodi[]" id="selected_prodi" class="form-select" multiple size="3">
                @foreach ($prodiOptions as $prodi)
                    <option value="{{ $prodi->authorised_value }}"
                        {{ in_array($prodi->authorised_value, $selectedProdiCodes) ? 'selected' : '' }}>
                        {{ $prodi->lib }}
                    </option>
                @endforeach
            </select>

        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if (!$dataExists)
        <div class="alert alert-info text-center" role="alert">
            Tidak ada data peminjaman untuk program studi yang dipilih pada tahun {{ $selectedYear }}.
        </div>
    @else
        {{-- Card untuk Grafik --}}
        <div class="card mt-4">
            <div class="card-header">
                Grafik Statistik Peminjaman per Program Studi (Tahun {{ $selectedYear }})
            </div>
            <div class="card-body">
                <canvas id="peminjamanProdiChart"></canvas>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <p class="text-muted">
                        Total Data: {{ $statistics->count() }} entri
                    </p>
                    &nbsp;

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Periode (Bulan)</th>
                                <th>Program Studi</th>
                                <th>Jumlah Buku Terpinjam</th>
                                <th>Jumlah Peminjam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statistics as $stat)
                                <tr>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $stat->periode)->format('M Y') }}
                                    </td>
                                    <td>{{ $stat->prodi_name }} ({{ $stat->prodi_code }})</td>
                                    <td>{{ $stat->jumlah_buku_terpinjam }}</td>
                                    <td>{{ $stat->jumlah_peminjam_unik }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Total Keseluruhan --}}
                <div class="d-flex justify-content-end mt-3">
                    <p class="h5">Total Buku Terpinjam:
                        <strong>{{ $statistics->sum('jumlah_buku_terpinjam') }}</strong>
                    </p>
                </div>
                <div class="d-flex justify-content-end">
                    <p class="h5">Total Peminjam:
                        <strong>{{ $statistics->sum('jumlah_peminjam_unik') }}</strong>
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Sertakan Chart.js (jika belum di layout utama) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if ($dataExists)
            const ctx = document.getElementById('peminjamanProdiChart').getContext('2d');

            const labels = @json($chartLabels);
            const datasets = @json($chartDatasets);

            new Chart(ctx, {
                type: 'line', // Gunakan line chart untuk melihat tren per bulan
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Tren Peminjaman per Program Studi'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan dan Tahun'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah'
                            }
                        }
                    }
                }
            });
        @endif
    });
</script>
@endsection
