@extends('layouts.app')

@section('title', 'Laporan Kunjungan Prodi')
@section('content')
    <div class="container">
        <h4>Laporan Kunjungan</h4>

        {{-- Form Filter Dinamis (Per Hari & Per Tahun) --}}
        <form method="GET" action="{{ route('kunjungan.prodiTable') }}" class="row g-3 mb-4 align-items-end" id="filterForm">
            <div class="col-md-auto">
                <label for="filter_type" class="form-label">Tampilkan Data:</label>
                <select name="filter_type" id="filter_type" class="form-select">
                    <option value="daily" {{ ($filterType ?? 'daily') == 'daily' ? 'selected' : '' }}>Per Hari</option>
                    <option value="yearly" {{ ($filterType ?? '') == 'yearly' ? 'selected' : '' }}>Per Tahun</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="prodi" class="form-label">Pilih Prodi/Tipe User</label>
                <select name="prodi" id="prodi" class="form-select">
                    {{-- <option value="">-- Semua Prodi & Dosen/Tendik --</option> --}}
                    @foreach ($listProdi as $kode => $nama)
                        <option value="{{ $kode }}" {{ request('prodi') == $kode ? 'selected' : '' }}>
                            ({{ $kode }})
                            -- {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            {{-- Daily Filter Inputs --}}
            <div class="col-md-3" id="dailyFilterStart"
                style="{{ ($filterType ?? 'daily') == 'daily' ? '' : 'display: none;' }}">
                <label for="tanggal_awal" class="form-label">Tanggal Awal:</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                    value="{{ $tanggalAwal ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}">
            </div>
            <div class="col-md-3" id="dailyFilterEnd"
                style="{{ ($filterType ?? 'daily') == 'daily' ? '' : 'display: none;' }}">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir:</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                    value="{{ $tanggalAkhir ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}">
            </div>
            {{-- Yearly Filter Input --}}
            <div class="col-md-2" id="yearlyFilterYear"
                style="{{ ($filterType ?? '') == 'yearly' ? '' : 'display: none;' }}">
                <label for="tahun" class="form-label">Tahun</label>
                <select name="tahun" id="tahun" class="form-select">
                    <option value="">-- Pilih Tahun --</option>
                    @php
                        $currentYear = \Carbon\Carbon::now()->year;
                        $selectedYear = request('tahun') ?? $currentYear;
                        for ($year = $currentYear; $year >= 2020; $year--) {
                            echo "<option value='{$year}' " .
                                ((string) $year === (string) $selectedYear ? 'selected' : '') .
                                ">{$year}</option>";
                        }
                    @endphp
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <div class="d-flex justify-content-end">
            <button type="button" id="downloadFullExcel" class="btn btn-success mt-3 mb-2">
                <i class="fas fa-file-csv"></i> Export ke CSV
            </button>
        </div>

        {{-- Tabel Laporan Kunjungan --}}
        <div class="table-responsive" id="tabelLaporan">
            <table class="table table-bordered table-striped" id="myTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>
                            @if (($filterType ?? 'daily') == 'yearly')
                                Bulan
                            @else
                                Tanggal Kunjungan
                            @endif
                        </th>
                        <th>Kode Identifikasi</th>
                        <th>Tipe User / Nama Prodi</th>
                        <th>Jumlah Kunjungan</th>
                        <th>Detail Pengunjung</th> {{-- Kolom ini sekarang selalu ada --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $index => $row)
                        <tr>
                            <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td>
                                @if (($filterType ?? 'daily') == 'yearly')
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $row->bulan)->locale('id')->isoFormat('MMMM YYYY') }}
                                @else
                                    {{ \Carbon\Carbon::parse($row->tanggal_kunjungan)->format('d F Y') }}
                                @endif
                            </td>
                            <td>{{ $row->kode_prodi }}</td>
                            <td>{{ $row->nama_prodi }}</td>
                            <td>{{ $row->jumlah_kunjungan }}</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm view-detail-btn"
                                    data-filter-type="{{ $filterType }}"
                                    @if (($filterType ?? 'daily') == 'yearly') data-bulan-tahun="{{ $row->bulan }}"
                                    @else
                                        data-tanggal="{{ $row->tanggal_kunjungan }}" @endif
                                    data-kode-identifikasi="{{ $row->kode_identifikasi }}">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada data kunjungan ditemukan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination dan Total Kunjungan --}}
        <div class="mt-3">
            {{ $data->links() }}
            <p class="mt-3">Total Kunjungan: {{ $totalKeseluruhanKunjungan }}</p>
        </div>
    </div>

    {{-- Modal untuk Detail Pengunjung --}}
    <div class="modal fade" id="detailPengunjungModal" tabindex="-1" aria-labelledby="detailPengunjungModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailPengunjungModalLabel">Detail Pengunjung</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Periode:</strong> <span id="modalPeriode"></span></p>
                    <p><strong>Kode/Tipe:</strong> <span id="modalKodeTipe"></span></p>
                    <h6>Daftar Nama Pengunjung:</h6>
                    <ul id="daftarNamaPengunjung" class="list-group">
                        <li class="list-group-item text-center" id="loadingMessage" style="display: none;">Memuat data...
                        </li>
                        <li class="list-group-item text-center" id="noDataMessage" style="display: none;">Tidak ada data
                            pengunjung.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
        <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Skrip untuk Logika Filter Dinamis (Kode ini tidak berubah)
                const filterTypeSelect = document.getElementById('filter_type');
                const dailyFilterStart = document.getElementById('dailyFilterStart');
                const dailyFilterEnd = document.getElementById('dailyFilterEnd');
                const yearlyFilterYear = document.getElementById('yearlyFilterYear');

                function toggleFilters() {
                    if (filterTypeSelect.value === 'daily') {
                        dailyFilterStart.style.display = 'block';
                        dailyFilterEnd.style.display = 'block';
                        yearlyFilterYear.style.display = 'none';
                    } else if (filterTypeSelect.value === 'yearly') {
                        dailyFilterStart.style.display = 'none';
                        dailyFilterEnd.style.display = 'none';
                        yearlyFilterYear.style.display = 'block';
                    }
                }

                filterTypeSelect.addEventListener('change', toggleFilters);
                toggleFilters();

                // Skrip untuk Export ke CSV (Kode yang diperbarui)
                // const downloadFullExcelButton = document.getElementById("downloadFullExcel");
                // if (downloadFullExcelButton) {
                //     downloadFullExcelButton.addEventListener("click", async function() {
                //         // Mengambil parameter filter dari URL saat ini
                //         const urlParams = new URLSearchParams(window.location.search);
                //         const filterType = urlParams.get('filter_type') || 'daily';
                //         const prodiFilter = urlParams.get('prodi') || '';

                //         let url =
                //             `{{ route('kunjungan.get_prodi_export_data') }}?filter_type=${filterType}`;

                //         if (filterType === 'daily') {
                //             const tanggalAwal = urlParams.get('tanggal_awal');
                //             const tanggalAkhir = urlParams.get('tanggal_akhir');
                //             if (!tanggalAwal || !tanggalAkhir) {
                //                 alert("Mohon pilih tanggal awal dan tanggal akhir terlebih dahulu.");
                //                 return;
                //             }
                //             url += `&tanggal_awal=${tanggalAwal}&tanggal_akhir=${tanggalAkhir}`;
                //         } else if (filterType === 'yearly') {
                //             const tahun = urlParams.get('tahun');
                //             if (!tahun) {
                //                 alert("Mohon pilih tahun terlebih dahulu.");
                //                 return;
                //             }
                //             url += `&tahun=${tahun}`;
                //         }

                //         if (prodiFilter) {
                //             url += `&prodi=${prodiFilter}`;
                //         }

                //         try {
                //             const response = await fetch(url);
                //             const result = await response.json();

                //             if (response.ok) {
                //                 if (result.data.length === 0) {
                //                     alert("Tidak ada data untuk diekspor dalam rentang filter ini.");
                //                     return;
                //                 }

                //                 let csv = [];
                //                 const delimiter = ';';
                //                 const headers = ['No', 'Tanggal Kunjungan', 'Kode Identifikasi',
                //                     'Tipe User / Nama Prodi', 'Jumlah Kunjungan'
                //                 ];
                //                 csv.push(headers.join(delimiter));

                //                 result.data.forEach((row, index) => {
                //                     let formattedTanggal;
                //                     if (filterType === 'yearly') {
                //                         formattedTanggal = row
                //                             .bulan; // Gunakan bulan jika filter yearly
                //                     } else {
                //                         formattedTanggal = new Date(row.tanggal_kunjungan)
                //                             .toLocaleDateString('id-ID', {
                //                                 year: 'numeric',
                //                                 month: '2-digit',
                //                                 day: '2-digit'
                //                             });
                //                     }

                //                     const rowData = [
                //                         (index + 1),
                //                         formattedTanggal,
                //                         `"${row.kode_identifikasi.replace(/"/g, '""')}"`,
                //                         `"${row.nama_prodi.replace(/"/g, '""')}"`,
                //                         row.jumlah_kunjungan
                //                     ];
                //                     csv.push(rowData.join(delimiter));
                //                 });

                //                 const csvString = csv.join('\n');
                //                 const BOM = "\uFEFF"; // Byte Order Mark untuk encoding UTF-8
                //                 const blob = new Blob([BOM + csvString], {
                //                     type: 'text/csv;charset=utf-8;'
                //                 });

                //                 const link = document.createElement("a");

                //                 let fileName =
                //                     (() => {
                //                         if (filterType === 'daily') {
                //                             const tanggalAwal = urlParams.get('tanggal_awal');
                //                             const tanggalAkhir = urlParams.get('tanggal_akhir');
                //                             let range = `${tanggalAwal}_sampai_${tanggalAkhir}`;
                //                             if (prodiFilter) {
                //                                 return `laporan_kunjungan_${prodiFilter}_${filterType}_${range}.csv`;
                //                             } else {
                //                                 return `laporan_kunjungan_${filterType}_${range}.csv`;
                //                             }
                //                         } else {
                //                             // yearly
                //                             const tahun = urlParams.get('tahun');
                //                             if (prodiFilter) {
                //                                 return `laporan_kunjungan_${prodiFilter}_${filterType}_${tahun}.csv`;
                //                             } else {
                //                                 return `laporan_kunjungan_${filterType}_${tahun}.csv`;
                //                             }
                //                         }
                //                     })();


                //                 if (navigator.msSaveBlob) {
                //                     navigator.msSaveBlob(blob, fileName);
                //                 } else {
                //                     link.href = URL.createObjectURL(blob);
                //                     link.download = fileName;
                //                     document.body.appendChild(link);
                //                     link.click();
                //                     document.body.removeChild(link);
                //                     URL.revokeObjectURL(link.href);
                //                 }
                //             } else {
                //                 alert(result.error || "Terjadi kesalahan saat mengambil data export.");
                //             }
                //         } catch (error) {
                //             console.error('Error fetching export data:', error);
                //             alert("Terjadi kesalahan teknis saat mencoba mengekspor data.");
                //         }
                //     });
                // }

                const downloadFullExcelButton = document.getElementById("downloadFullExcel");
                if (downloadFullExcelButton) {
                    downloadFullExcelButton.addEventListener("click", async function() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const filterType = urlParams.get('filter_type') || 'daily';
                        const prodiFilter = urlParams.get('prodi') || '';

                        // Mendapatkan nama prodi yang dipilih dari dropdown
                        const prodiSelect = document.getElementById('prodi');
                        const prodiName = prodiSelect.options[prodiSelect.selectedIndex].text.split('--')[1]
                            .trim();

                        let url =
                            `{{ route('kunjungan.get_prodi_export_data') }}?filter_type=${filterType}`;
                        let judul = '';

                        if (filterType === 'daily') {
                            const tanggalAwal = urlParams.get('tanggal_awal');
                            const tanggalAkhir = urlParams.get('tanggal_akhir');
                            if (!tanggalAwal || !tanggalAkhir) {
                                alert("Mohon pilih tanggal awal dan tanggal akhir terlebih dahulu.");
                                return;
                            }
                            url += `&tanggal_awal=${tanggalAwal}&tanggal_akhir=${tanggalAkhir}`;
                            judul =
                                `Laporan Kunjungan Prodi: ${prodiName} - Periode: ${tanggalAwal} s/d ${tanggalAkhir}`;

                        } else if (filterType === 'yearly') {
                            const tahun = urlParams.get('tahun');
                            if (!tahun) {
                                alert("Mohon pilih tahun terlebih dahulu.");
                                return;
                            }
                            url += `&tahun=${tahun}`;
                            judul = `Laporan Kunjungan Prodi: ${prodiName} - Tahun: ${tahun}`;
                        }

                        if (prodiFilter) {
                            url += `&prodi=${prodiFilter}`;
                        }

                        try {
                            const response = await fetch(url);
                            const result = await response.json();

                            if (response.ok) {
                                if (result.data.length === 0) {
                                    alert("Tidak ada data untuk diekspor dalam rentang filter ini.");
                                    return;
                                }

                                let csv = [];
                                const delimiter = ';';
                                const headers = ['No', 'Tanggal Kunjungan', 'Kode Identifikasi',
                                    'Tipe User / Nama Prodi', 'Jumlah Kunjungan'
                                ];

                                // Tambahkan judul sebagai baris pertama
                                csv.push(`"${judul}"`);
                                csv.push(headers.join(delimiter));

                                let no = 1; // Inisialisasi nomor urut
                                result.data.forEach((row) => {
                                    let formattedTanggal;
                                    if (filterType === 'yearly') {
                                        // Gunakan format yang konsisten untuk tahunan
                                        formattedTanggal = new Date(row.bulan + '-01')
                                            .toLocaleDateString('id-ID', {
                                                month: 'long',
                                                year: 'numeric'
                                            });
                                    } else {
                                        // Gunakan format yang konsisten untuk harian
                                        formattedTanggal = new Date(row.tanggal_kunjungan)
                                            .toLocaleDateString('id-ID', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric'
                                            });
                                    }

                                    const rowData = [
                                        no++, // Tambahkan nomor urut dan inkremen
                                        `"${formattedTanggal.replace(/"/g, '""')}"`,
                                        `"${row.kode_identifikasi.replace(/"/g, '""')}"`,
                                        `"${row.nama_prodi.replace(/"/g, '""')}"`,
                                        row.jumlah_kunjungan
                                    ];
                                    csv.push(rowData.join(delimiter));
                                });

                                const csvString = csv.join('\n');
                                const BOM = "\uFEFF";
                                const blob = new Blob([BOM + csvString], {
                                    type: 'text/csv;charset=utf-8;'
                                });

                                const link = document.createElement("a");
                                let fileName = (() => {
                                    if (filterType === 'daily') {
                                        const tanggalAwal = urlParams.get('tanggal_awal');
                                        const tanggalAkhir = urlParams.get('tanggal_akhir');
                                        let range = `${tanggalAwal}_sampai_${tanggalAkhir}`;
                                        if (prodiFilter) {
                                            return `laporan_kunjungan_${prodiFilter}_${filterType}_${range}.csv`;
                                        } else {
                                            return `laporan_kunjungan_${filterType}_${range}.csv`;
                                        }
                                    } else { // yearly
                                        const tahun = urlParams.get('tahun');
                                        if (prodiFilter) {
                                            return `laporan_kunjungan_${prodiFilter}_${filterType}_${tahun}.csv`;
                                        } else {
                                            return `laporan_kunjungan_${filterType}_${tahun}.csv`;
                                        }
                                    }
                                })();

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

                // Skrip untuk Pop-up Modal Detail Pengunjung (Kode ini tidak berubah)
                const detailModal = new bootstrap.Modal(document.getElementById('detailPengunjungModal'));
                const daftarNamaPengunjungUl = document.getElementById('daftarNamaPengunjung');
                const modalPeriodeSpan = document.getElementById('modalPeriode');
                const modalKodeTipeSpan = document.getElementById('modalKodeTipe');
                const loadingMessage = document.getElementById('loadingMessage');
                const noDataMessage = document.getElementById('noDataMessage'); // Ambil elemen baru ini

                document.querySelectorAll('.view-detail-btn').forEach(button => {
                    button.addEventListener('click', async function() {
                        const filterType = this.dataset.filterType;
                        const kodeIdentifikasi = this.dataset.kodeIdentifikasi;

                        // Reset modal
                        daftarNamaPengunjungUl.innerHTML = '';
                        loadingMessage.style.display = 'block';
                        noDataMessage.style.display = 'none';
                        daftarNamaPengunjungUl.appendChild(loadingMessage);

                        let url;
                        let periodeText;

                        if (filterType === 'yearly') {
                            const bulanTahun = this.dataset.bulanTahun;
                            url =
                                `{{ route('kunjungan.get_detail_pengunjung') }}?bulan=${bulanTahun}&kode_identifikasi=${kodeIdentifikasi}`;
                            periodeText = new Date(`${bulanTahun}-01`).toLocaleDateString('id-ID', {
                                month: 'long',
                                year: 'numeric'
                            });
                        } else {
                            const tanggalKunjungan = this.dataset.tanggal;
                            url =
                                `{{ route('kunjungan.get_detail_pengunjung') }}?tanggal=${tanggalKunjungan}&kode_identifikasi=${kodeIdentifikasi}`;
                            periodeText = new Date(tanggalKunjungan).toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric'
                            });
                        }

                        modalPeriodeSpan.textContent = periodeText;
                        modalKodeTipeSpan.textContent = kodeIdentifikasi;
                        detailModal.show();

                        try {
                            const response = await fetch(url);
                            const data = await response.json();

                            loadingMessage.style.display = 'none';
                            daftarNamaPengunjungUl.innerHTML = ''; // Kosongkan lagi sebelum diisi

                            if (data.length > 0) {
                                data.forEach(pengunjung => {
                                    const listItem = document.createElement('li');
                                    listItem.className =
                                        'list-group-item list-group-item-light border-start-0 border-end-0 border-bottom-0';
                                    listItem.innerHTML =
                                        `${pengunjung.nama} (${pengunjung.cardnumber}) - <span class="badge bg-secondary ms-2"> ${pengunjung.visit_count} Kunjungan</span>`;
                                    daftarNamaPengunjungUl.appendChild(listItem);
                                });
                            } else {
                                noDataMessage.style.display = 'block';
                                daftarNamaPengunjungUl.appendChild(noDataMessage);
                            }

                        } catch (error) {
                            console.error('Error fetching detail data:', error);
                            loadingMessage.style.display = 'none';
                            daftarNamaPengunjungUl.innerHTML = '';
                            const errorItem = document.createElement('li');
                            errorItem.classList.add('list-group-item', 'list-group-item-danger');
                            errorItem.textContent = 'Terjadi kesalahan saat memuat data.';
                            daftarNamaPengunjungUl.appendChild(errorItem);
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
