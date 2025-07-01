@extends('layouts.app') {{-- Sesuaikan dengan layout aplikasi Anda --}}

@section('content')
@section('title', 'Statistik Peminjaman')
<div class="container">
    <h4>Statistik Peminjaman</h4>

    {{-- Form Filter --}}
    <form action="{{ route('peminjaman.peminjaman_rentang_tanggal') }}" method="GET"
        class="row g-3 mb-4 align-items-end">

        {{-- Filter Type Dropdown --}}
        <div class="col-md-auto">
            <label for="filter_type" class="form-label">Tampilkan Data:</label>
            <select name="filter_type" id="filter_type" class="form-select">
                <option value="daily" {{ $filterType == 'daily' ? 'selected' : '' }}>Per Hari</option>
                <option value="monthly" {{ $filterType == 'monthly' ? 'selected' : '' }}>Per Bulan</option>
            </select>
        </div>

        {{-- Daily Filter Inputs (akan ditampilkan/disembunyikan oleh JS) --}}
        <div class="col-md-3" id="dailyFilterStart" style="{{ $filterType == 'daily' ? '' : 'display: none;' }}">
            <label for="start_date" class="form-label">Tanggal Awal:</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ?? '' }}">
        </div>
        <div class="col-md-3" id="dailyFilterEnd" style="{{ $filterType == 'daily' ? '' : 'display: none;' }}">
            <label for="end_date" class="form-label">Tanggal Akhir:</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ?? '' }}">
        </div>

        {{-- Monthly Filter Input (akan ditampilkan/disembunyikan oleh JS) --}}
        <div class="col-md-3" id="monthlyFilter" style="{{ $filterType == 'monthly' ? '' : 'display: none;' }}">
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

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
    </form>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if ($statistics->isEmpty())
        <div class="alert alert-info text-center" role="alert">
            Tidak ada data peminjaman untuk
            @if ($filterType == 'daily')
                rentang tanggal {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                sampai {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}.
            @else
                tahun {{ $selectedYear }}.
            @endif
        </div>
    @else
        {{-- Card untuk Grafik --}}
        <div class="card mt-4">
            <div class="card-header">
                Grafik Statistik Peminjaman @if ($filterType == 'daily')
                    per Hari
                @else
                    per Bulan
                @endif
            </div>
            <div class="card-body">
                <canvas id="peminjamanChart"></canvas>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Periode</th> {{-- Kolom ini sekarang dinamis --}}
                                <th>Jumlah Buku Terpinjam</th>
                                <th>Jumlah Peminjam</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statistics as $stat)
                                <tr>
                                    <td>
                                        @if ($filterType == 'daily')
                                            {{ \Carbon\Carbon::parse($stat->tanggal)->format('d M Y') }}
                                        @else
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $stat->periode)->format('M Y') }}
                                        @endif
                                    </td>
                                    <td>{{ $stat->jumlah_peminjaman_buku }}</td>
                                    <td>{{ $stat->jumlah_peminjam_unik }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- Total Keseluruhan --}}
                <div class="d-flex justify-content-end mt-3">
                    <p class="h5">Total Buku Terpinjam:
                        <strong>{{ $statistics->sum('jumlah_peminjaman_buku') }}</strong>
                    </p>
                </div>
                <div class="d-flex justify-content-end">
                    <p class="h5">Total Peminjam:
                        <strong>{{ $statistics->sum('jumlah_peminjam_unik') }}</strong>
                    </p>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    {{-- Pagination hanya relevan jika data harian --}}
                    @if ($filterType == 'daily')
                        {{ $statistics->links() }}
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>

{{-- Sertakan Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterTypeSelect = document.getElementById('filter_type');
        const dailyFilterStart = document.getElementById('dailyFilterStart');
        const dailyFilterEnd = document.getElementById('dailyFilterEnd');
        const monthlyFilter = document.getElementById('monthlyFilter');

        function toggleFilterInputs() {
            const selectedValue = filterTypeSelect.value;
            if (selectedValue === 'daily') {
                dailyFilterStart.style.display = 'block';
                dailyFilterEnd.style.display = 'block';
                monthlyFilter.style.display = 'none';
            } else { // monthly
                dailyFilterStart.style.display = 'none';
                dailyFilterEnd.style.display = 'none';
                monthlyFilter.style.display = 'block';
            }
        }

        toggleFilterInputs();

        filterTypeSelect.addEventListener('change', toggleFilterInputs);

        @if (!$statistics->isEmpty())
            const ctx = document.getElementById('peminjamanChart').getContext('2d');

            const labels = @json($chartLabels);
            const dataBooks = @json($chartDataBooks);
            const dataBorrowers = @json($chartDataBorrowers);
            const chartTitle =
                "Grafik Statistik Peminjaman @if ($filterType == 'daily') per Hari @else per Bulan @endif";
            const xAxisTitle =
                "@if ($filterType == 'daily') Tanggal @else Bulan dan Tahun @endif";

            new Chart(ctx, {
                type: 'line', 
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Jumlah Buku Terpinjam',
                            data: dataBooks,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1,
                            fill: false
                        },
                        {
                            label: 'Jumlah Peminjam',
                            data: dataBorrowers,
                            borderColor: 'rgba(153, 102, 255, 1)',
                            backgroundColor: 'rgba(153, 102, 255, 0.2)',
                            tension: 0.1,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: chartTitle
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: xAxisTitle
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
