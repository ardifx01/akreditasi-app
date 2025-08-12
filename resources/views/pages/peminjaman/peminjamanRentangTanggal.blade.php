@extends('layouts.app')

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

        <div class="col-md-3" id="monthlyFilter" style="{{ $filterType == 'monthly' ? '' : 'display: none;' }}">
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
            <div class="card-header d-flex justify-content-between align-items-center">
                Ringkasan Data Peminjaman
                <button type="button" id="exportCsvBtn" class="btn btn-success btn-sm"><i class="fas fa-file-csv"></i>
                    Export CSV</button>
            </div>
            <div class="card-body">
                {{-- <div class="table-responsive">
                    <p class="text-muted">
                        Total Data: <span class="badge bg-secondary">{{ $statistics->count() }} entri</span>
                    </p>
                    <table class="table table-bordered table-striped" id="peminjamanTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Periode</th>
                                <th>Jumlah Buku Terpinjam</th>
                                <th>Jumlah Peminjam</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statistics as $index => $stat)
                                <tr>
                                    <td>
                                        @if ($filterType == 'daily')
                                            {{ $statistics->firstItem() + $index }}
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($filterType == 'daily')
                                            {{ \Carbon\Carbon::parse($stat->tanggal)->format('d M Y') }}
                                        @else
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $stat->periode)->format('M Y') }}
                                        @endif
                                    </td>
                                    <td>{{ $stat->jumlah_peminjaman_buku }}</td>
                                    <td>{{ $stat->jumlah_peminjam_unik }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm view-details-btn"
                                            data-bs-toggle="modal" data-bs-target="#detailsModal"
                                            data-details='@json($stat->details)'
                                            data-periode="{{ $filterType == 'daily' ? \Carbon\Carbon::parse($stat->tanggal)->format('d M Y') : \Carbon\Carbon::createFromFormat('Y-m', $stat->periode)->format('M Y') }}">
                                            <i class="fas fa-eye"></i> Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="kunjunganTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Periode</th>
                                <th>Jumlah Buku Terpinjam</th>
                                <th>Jumlah Peminjam</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($statistics as $index => $stat)
                                <tr>
                                    <td>{{ $index + 1 }}</td> {{-- Langsung cetak index, nanti diatur ulang oleh Datatables --}}
                                    <td>
                                        @if ($filterType == 'daily')
                                            {{ \Carbon\Carbon::parse($stat->tanggal)->format('d M Y') }}
                                        @else
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $stat->periode)->format('M Y') }}
                                        @endif
                                    </td>
                                    <td>{{ $stat->jumlah_peminjaman_buku }}</td>
                                    <td>{{ $stat->jumlah_peminjam_unik }}</td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm view-details-btn"
                                            data-bs-toggle="modal" data-bs-target="#detailsModal"
                                            data-details='@json($stat->details)'
                                            data-periode="{{ $filterType == 'daily' ? \Carbon\Carbon::parse($stat->tanggal)->format('d M Y') : \Carbon\Carbon::createFromFormat('Y-m', $stat->periode)->format('M Y') }}">
                                            <i class="fas fa-eye"></i> Lihat Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- MODAL POP-UP UNTUK DETAIL PEMINJAMAN --}}
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Detail Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="modal-periode-title"></h6>
                <ul class="list-group" id="detailsList">
                    {{-- Konten detail akan dimasukkan di sini oleh JavaScript --}}
                </ul>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <button type="button" id="prevPageBtn" class="btn btn-secondary">Sebelumnya</button>
                <span id="pageInfo" class="text-muted"></span>
                <button type="button" id="nextPageBtn" class="btn btn-secondary">Selanjutnya</button>
                <button type="button" class="btn btn-primary ms-auto" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#kunjunganTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
            },
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "dom": '<"d-flex justify-content-between mb-3"lp>t<"d-flex justify-content-between mt-3"ip>',
            "lengthMenu": [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "Semua"]
            ],
            "pageLength": 10,
            "columnDefs": [{
                "orderable": false,
                "targets": [0, 4]
            }, ]
        });


        const filterTypeSelect = document.getElementById('filter_type');
        const dailyFilterStart = document.getElementById('dailyFilterStart');
        const dailyFilterEnd = document.getElementById('dailyFilterEnd');
        const monthlyFilter = document.getElementById('monthlyFilter');
        const exportCsvBtn = document.getElementById('exportCsvBtn');

        function toggleFilterInputs() {
            const selectedValue = filterTypeSelect.value;
            if (selectedValue === 'daily') {
                dailyFilterStart.style.display = 'block';
                dailyFilterEnd.style.display = 'block';
                monthlyFilter.style.display = 'none';
            } else {
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
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: 'Jumlah Buku Terpinjam',
                            data: dataBooks,
                            borderColor: 'rgba(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192)',
                            tension: 0.1,
                            fill: false
                        },
                        {
                            label: 'Jumlah Peminjam',
                            data: dataBorrowers,
                            borderColor: 'rgba(153, 102, 255)',
                            backgroundColor: 'rgba(153, 102, 255)',
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

        // ===============================================
        // KODE JAVASCRIPT BARU UNTUK PAGINATION DI MODAL (MODIFIKASI DARI SEBELUMNYA)
        // ===============================================

        const detailsModal = document.getElementById('detailsModal');
        const detailsList = document.getElementById('detailsList');
        const modalPeriodeTitle = document.getElementById('modal-periode-title');
        const prevPageBtn = document.getElementById('prevPageBtn');
        const nextPageBtn = document.getElementById('nextPageBtn');
        const pageInfo = document.getElementById('pageInfo');

        let currentPage = 1;
        const itemsPerPage = 5; // Jumlah item per halaman
        let currentDetailsData = [];

        function renderDetails(page) {
            detailsList.innerHTML = '';
            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const paginatedItems = currentDetailsData.slice(startIndex, endIndex);

            paginatedItems.forEach(detail => {
                const listItem = document.createElement('li');
                listItem.className =
                    'list-group-item d-flex justify-content-between align-items-center';
                listItem.innerHTML = `
                    <div>
                        <strong>Peminjam:</strong> ${detail.nama_peminjam} (${detail.cardnumber}) <br>
                        <strong>Judul Buku:</strong> ${detail.judul_buku}
                    </div>
                    <span class="badge bg-secondary rounded-pill">ID Buku: ${detail.barcode}</span>
                `;
                detailsList.appendChild(listItem);
            });

            const totalPages = Math.ceil(currentDetailsData.length / itemsPerPage);
            prevPageBtn.disabled = page === 1;
            nextPageBtn.disabled = page === totalPages || totalPages === 0;
            pageInfo.textContent = `Halaman ${page} dari ${totalPages}`;
        }

        if (detailsModal) {
            detailsModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const details = JSON.parse(button.getAttribute('data-details'));
                const periode = button.getAttribute('data-periode');

                currentDetailsData = details;
                currentPage = 1;

                modalPeriodeTitle.textContent = 'Detail Peminjaman pada: ' + periode;

                renderDetails(currentPage);
            });
        }

        prevPageBtn.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderDetails(currentPage);
            }
        });

        nextPageBtn.addEventListener('click', function() {
            const totalPages = Math.ceil(currentDetailsData.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderDetails(currentPage);
            }
        });

        // ===============================================
        // KODE JAVASCRIPT BARU UNTUK EKSPOR CSV LENGKAP
        // ===============================================
        // if (exportCsvBtn) {
        //     exportCsvBtn.addEventListener('click', function() {
        //         // Ambil data lengkap yang dikirim dari controller
        //         const dataToExport = @json($fullStatistics);
        //         if (!dataToExport || dataToExport.length === 0) {
        //             alert("Tidak ada data untuk diekspor.");
        //             return;
        //         }

        //         let csv = [];
        //         const delimiter = ';';

        //         // Tentukan header, tambahkan "Nomor"
        //         let headers = ['No', 'Periode', 'Jumlah Buku Terpinjam', 'Jumlah Peminjam'];
        //         csv.push(headers.join(delimiter));

        //         // Tambahkan data dengan nomor urut
        //         dataToExport.forEach((row, index) => {
        //             let periode = '';
        //             if (row.periode) {
        //                 periode = new Date(row.periode).toLocaleDateString('id-ID', {
        //                     year: 'numeric',
        //                     month: 'short'
        //                 });
        //             } else if (row.tanggal) {
        //                 periode = new Date(row.tanggal).toLocaleDateString('id-ID', {
        //                     year: 'numeric',
        //                     month: 'short',
        //                     day: 'numeric'
        //                 });
        //             }

        //             let rowData = [
        //                 index + 1, // Nomor
        //                 periode,
        //                 row.jumlah_peminjaman_buku,
        //                 row.jumlah_peminjam_unik
        //             ];
        //             csv.push(rowData.join(delimiter));
        //         });

        //         const csvString = csv.join('\n');
        //         const BOM = "\uFEFF";
        //         const blob = new Blob([BOM + csvString], {
        //             type: 'text/csv;charset=utf-8;'
        //         });

        //         const link = document.createElement("a");
        //         const filterType = filterTypeSelect.value;
        //         let fileName = 'statistik_peminjaman';

        //         if (filterType === 'daily') {
        //             const startDate = document.getElementById('start_date').value;
        //             const endDate = document.getElementById('end_date').value;
        //             fileName += `_${startDate}_${endDate}`;
        //         } else {
        //             const selectedYear = document.getElementById('selected_year').value;
        //             fileName += `_${selectedYear}`;
        //         }
        //         fileName += `_${new Date().toISOString().slice(0,10).replace(/-/g,'')}.csv`;


        //         if (navigator.msSaveBlob) {
        //             navigator.msSaveBlob(blob, fileName);
        //         } else {
        //             link.href = URL.createObjectURL(blob);
        //             link.download = fileName;
        //             document.body.appendChild(link);
        //             link.click();
        //             document.body.removeChild(link);
        //             URL.revokeObjectURL(link.href);
        //         }
        //     });
        // }


        if (exportCsvBtn) {
            exportCsvBtn.addEventListener('click', function() {
                // Ambil data lengkap yang dikirim dari controller
                const dataToExport = @json($fullStatistics);
                if (!dataToExport || dataToExport.length === 0) {
                    alert("Tidak ada data untuk diekspor.");
                    return;
                }

                let csv = [];
                const delimiter = ';';
                const filterType = filterTypeSelect.value;
                let title = "Laporan Statistik Peminjaman";

                // Tentukan judul berdasarkan filter
                if (filterType === 'daily') {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    title += ` Harian: ${startDate} sampai ${endDate}`;
                } else {
                    const selectedYear = document.getElementById('selected_year').value;
                    title += ` Bulanan Tahun ${selectedYear}`;
                }
                csv.push(title); // Tambahkan judul ke baris pertama

                // Tambahkan baris kosong sebagai pemisah
                csv.push('');

                // Tentukan header, tambahkan "Nomor"
                let headers = ['No', 'Periode', 'Jumlah Buku Terpinjam', 'Jumlah Peminjam'];
                csv.push(headers.join(delimiter));

                // Tambahkan data dengan nomor urut
                dataToExport.forEach((row, index) => {
                    let periode = '';
                    if (row.periode) {
                        periode = new Date(row.periode).toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'short'
                        });
                    } else if (row.tanggal) {
                        periode = new Date(row.tanggal).toLocaleDateString('id-ID', {
                            year: 'numeric',
                            month: 'short',
                            day: 'numeric'
                        });
                    }

                    let rowData = [
                        index + 1, // Nomor
                        periode,
                        row.jumlah_peminjaman_buku,
                        row.jumlah_peminjam_unik
                    ];
                    csv.push(rowData.join(delimiter));
                });

                const csvString = csv.join('\n');
                const BOM = "\uFEFF";
                const blob = new Blob([BOM + csvString], {
                    type: 'text/csv;charset=utf-8;'
                });

                const link = document.createElement("a");

                // Nama file tetap sama, tidak perlu diubah.
                let fileName = 'statistik_peminjaman';

                if (filterType === 'daily') {
                    const startDate = document.getElementById('start_date').value;
                    const endDate = document.getElementById('end_date').value;
                    fileName += `_${startDate}_${endDate}`;
                } else {
                    const selectedYear = document.getElementById('selected_year').value;
                    fileName += `_${selectedYear}`;
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
