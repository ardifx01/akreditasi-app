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
        {{-- Card untuk Grafik Chart --}}
        <div class="card mt-4 shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-muted">Grafik Statistik Peminjaman per Program Studi (Tahun
                    {{ $selectedYear }})</h6>
            </div>
            <div class="card-body">
                <canvas id="peminjamanProdiChart"></canvas>
            </div>
        </div>

        {{-- Card untuk Ringkasan Data Tabel --}}
        <div class="card mt-4 shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-muted">Ringkasan Data Peminjaman per Program Studi</h6>
                <button type="button" id="exportCsvBtn" class="btn btn-success btn-sm">Export CSV</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <p class="text-muted">
                        Total Data: {{ $statistics->count() }} entri
                    </p>
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
                                    <td>
                                        <a href="#" class="view-borrowers-link"
                                            data-periode-ym="{{ str_replace('-', '', $stat->periode) }}"
                                            data-prodi-code="{{ $stat->prodi_code }}">
                                            {{ $stat->jumlah_peminjam_unik }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total Keseluruhan</th>
                                <th>{{ $statistics->sum('jumlah_buku_terpinjam') }}</th>
                                <th>{{ $statistics->sum('jumlah_peminjam_unik') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Modal untuk Daftar Peminjam --}}
<div class="modal fade" id="borrowersModal" tabindex="-1" aria-labelledby="borrowersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="borrowersModalLabel">Daftar Peminjam untuk <span id="modalProdiName"></span>
                    (<span id="modalPeriod"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul id="borrowersList" class="list-group">
                </ul>
                <div id="loadingSpinner" class="text-center mt-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Memuat data...</p>
                </div>
                <div id="noDataMessage" class="alert alert-info mt-3" style="display:none;">
                    Tidak ada daftar peminjam untuk periode ini.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const exportCsvBtn = document.getElementById('exportCsvBtn');
            const borrowersModal = new bootstrap.Modal(document.getElementById('borrowersModal'));
            const borrowersList = document.getElementById('borrowersList');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const noDataMessage = document.getElementById('noDataMessage');
            const modalProdiName = document.getElementById('modalProdiName');
            const modalPeriod = document.getElementById('modalPeriod');

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

                    // Ambil header tabel
                    table.querySelectorAll('thead th').forEach(th => {
                        headers.push(th.innerText.trim());
                    });
                    csv.push(headers.join(delimiter));

                    // Ambil data baris
                    table.querySelectorAll('tbody tr').forEach(row => {
                        let rowData = [];
                        row.querySelectorAll('td').forEach(cell => {
                            let text = cell.innerText.trim();
                            text = text.replace(/"/g, '""');
                            if (text.includes(delimiter) || text.includes('"') || text
                                .includes('\n')) {
                                text =
                                    `"${text}"`;
                            }
                            rowData.push(text);
                        });
                        csv.push(rowData.join(delimiter));
                    });
                    // Tambahkan total dari tfoot
                    table.querySelectorAll('tfoot tr').forEach(row => {
                        let rowData = [];
                        row.querySelectorAll('th').forEach(cell => {
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

            document.querySelectorAll('.view-borrowers-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    borrowersList.innerHTML = '';
                    loadingSpinner.style.display = 'block';
                    noDataMessage.style.display = 'none';

                    const periodeYm = this.dataset.periodeYm;
                    const prodiCode = this.dataset.prodiCode;
                    const prodiNameDisplay = this.closest('tr').querySelector('td:nth-child(2)')
                        .innerText.split('(')[0].trim();
                    const periodDisplay = this.closest('tr').querySelector('td:first-child')
                        .innerText.trim();

                    modalProdiName.innerText = prodiNameDisplay;
                    modalPeriod.innerText = periodDisplay;

                    borrowersModal.show();
                    fetch(
                            `{{ route('peminjaman.peminjamDetail') }}?periode_ym=${periodeYm}&prodi_code=${prodiCode}`
                        )
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            loadingSpinner.style.display = 'none';
                            if (data.success && data.data && data.data.length > 0) {
                                data.data.forEach(borrower => {
                                    const li = document.createElement('li');
                                    li.className = 'list-group-item';
                                    li.textContent = borrower;
                                    borrowersList.appendChild(li);
                                });
                            } else {
                                noDataMessage.style.display =
                                    'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching borrower details:', error);
                            loadingSpinner.style.display = 'none';
                            borrowersList.innerHTML =
                                '<li class="list-group-item text-danger">Gagal memuat data peminjam.</li>';
                        });
                });
            });
        });
    </script>
@endpush
@endsection
