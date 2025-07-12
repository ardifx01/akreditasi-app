@extends('layouts.app')

@section('content')
@section('title', 'Statistik Peminjaman per Program Studi')
<div class="container">
    <h4>Statistik Peminjaman per Program Studi</h4>

    <form action="{{ route('peminjaman.peminjaman_prodi_chart') }}" method="GET" class="row g-3 mb-4 align-items-end">

        <div class="col-md-3">
            <label for="selected_year" class="form-label">Pilih Tahun:</label>
            <select name="selected_year" id="selected_year" class="form-select">
                @php
                    $currentYear = \Carbon\Carbon::now()->year;
                    $startYear = $currentYear - 5;
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
        <div class="card mt-4">
            <div class="card-header">
                Grafik Statistik Peminjaman per Program Studi (Tahun {{ $selectedYear }})
            </div>
            <div class="card-body">
                <canvas id="peminjamanProdiChart"></canvas>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Ringkasan Data Peminjaman per Program Studi

                <button type="button" id="exportCsvBtn" class="btn btn-success btn-sm">Export CSV</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <p class="text-muted">
                        Total Data: {{ $statistics->count() }} entri
                    </p>
                    &nbsp;

                    <table class="table table-bordered table-striped" id="prodiPeminjamanTable">
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportCsvBtn = document.getElementById('exportCsvBtn');

        @if ($dataExists)
            const ctx = document.getElementById('peminjamanProdiChart').getContext('2d');

            const labels = @json($chartLabels);
            const datasets = @json($chartDatasets);

            new Chart(ctx, {
                type: 'line',
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

        if (exportCsvBtn) {
            exportCsvBtn.addEventListener('click', function() {
                const table = document.getElementById('prodiPeminjamanTable');
                if (!table) {
                    console.error("Tabel dengan ID 'prodiPeminjamanTable' tidak ditemukan.");
                    alert("Tidak ada tabel data untuk diekspor.");
                    return;
                }

                let csv = [];
                const delimiter = ';';
                const headers = [];

                table.querySelectorAll('thead th').forEach(th => {
                    headers.push(th.innerText.trim());
                });
                csv.push(headers.join(delimiter));

                table.querySelectorAll('tbody tr').forEach(row => {
                    let rowData = [];
                    row.querySelectorAll('td').forEach(cell => {
                        let text = cell.innerText.trim();
                        text = text.replace(/"/g, '""');
                        if (text.includes(delimiter) || text.includes('"') || text
                            .includes('\n')) {
                            text = `"${text}"`;
                        }
                        rowData.push(text);
                    });
                    csv.push(rowData.join(delimiter));
                });

                const csvString = csv.join('\n');
                const BOM = "\uFEFF";
                const blob = new Blob([BOM + csvString], {
                    type: 'text/csv;charset=utf-8;'
                });

                const link = document.createElement("a");
                const selectedYear = document.getElementById('selected_year').value;
                let fileName = `statistik_peminjaman_prodi_${selectedYear}`;

                const selectedProdiSelect = document.getElementById('selected_prodi');
                const selectedProdiOptions = Array.from(selectedProdiSelect.selectedOptions);

                if (selectedProdiOptions.length > 0) {
                    let prodiNamesForFileName = selectedProdiOptions.map(option => {
                        let name = option.innerText.trim();
                        name = name.split('(')[0].trim().replace(/[^a-zA-Z0-9 ]/g, '').replace(
                            /\s+/g, '_');
                        return name;
                    });

                    if (prodiNamesForFileName.length <= 3) {
                        fileName += `_${prodiNamesForFileName.join('_')}`;
                    } else {
                        fileName += `_beberapa_prodi`;
                    }
                }

                fileName += `_${new Date().toISOString().slice(0,10).replace(/-/g,'')}.csv`;


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
            });
        }
    });
</script>
@endsection
