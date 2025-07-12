@extends('layouts.app')

@section('title', 'Laporan Kunjungan Prodi')
@section('content')
    <div class="container">
        <h4>Laporan Kunjungan</h4>

        <form method="GET" action="{{ route('kunjungan.prodiTable') }}" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="prodi" class="form-label">Pilih Prodi/Tipe User</label>
                <select name="prodi" id="prodi" class="form-select">
                    <option value="">-- Semua Prodi & Dosen/Tendik --</option>
                    <option value="DOSEN_TENDIK" {{ request('prodi') == 'DOSEN_TENDIK' ? 'selected' : '' }}>
                        -- Dosen / Tenaga Kependidikan --
                    </option>
                    @foreach ($listProdi as $kode => $nama)
                        <option value="{{ $kode }}" {{ request('prodi') == $kode ? 'selected' : '' }}>
                            ({{ $kode }})
                            -- {{ $nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control"
                    value="{{ request('tanggal_awal', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                    value="{{ request('tanggal_akhir', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        {{-- Tombol Download --}}
        <button id="downloadPng" class="btn btn-success mt-3 mb-2 me-2">Save Tabel (PNG)</button>
        <button type="button" id="downloadFullExcel" class="btn btn-warning mt-3 mb-2">Export ke CSV (Semua Data)</button>

        <div class="table-responsive" id="tabelLaporan">
            <table class="table table-bordered table-striped" id="myTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Kode Identifikasi</th>
                        <th>Tipe User / Nama Prodi</th>
                        <th>Jumlah Kunjungan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $index => $row)
                        <tr>
                            <td>{{ $loop->iteration + ($data->currentPage() - 1) * $data->perPage() }}</td>
                            <td>{{ \Carbon\Carbon::parse($row->tanggal_kunjungan)->format('d F Y') }}</td>
                            <td>{{ $row->kode_prodi }}</td>
                            <td>{{ $row->nama_prodi }}</td>
                            <td>{{ $row->jumlah_kunjungan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada data kunjungan ditemukan untuk filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $data->links() }}
            <p class="mt-3">Total Kunjungan: {{ $data->sum('jumlah_kunjungan') }}</p>
        </div>
    </div>


    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("downloadPng").addEventListener("click", function() {
                const element = document.getElementById("tabelLaporan");
                html2canvas(element, {
                    backgroundColor: "#ffffff",
                    useCORS: true
                }).then(canvas => {
                    const link = document.createElement("a");
                    link.download = "laporan_kunjungan_prodi_dosen.png";
                    link.href = canvas.toDataURL("image/png");
                    link.click();
                });
            });


            const downloadFullExcelButton = document.getElementById("downloadFullExcel");
            if (downloadFullExcelButton) {
                downloadFullExcelButton.addEventListener("click", async function() {
                    const prodiFilter = document.getElementById('prodi').value;
                    const tanggalAwal = document.getElementById('tanggal_awal').value;
                    const tanggalAkhir = document.getElementById('tanggal_akhir').value;

                    if (!tanggalAwal || !tanggalAkhir) {
                        alert("Mohon pilih tanggal awal dan tanggal akhir terlebih dahulu.");
                        return;
                    }

                    let url =
                        `{{ route('kunjungan.get_prodi_export_data') }}?tanggal_awal=${tanggalAwal}&tanggal_akhir=${tanggalAkhir}`;
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

                            const headers = ['Tanggal Kunjungan', 'Kode Identifikasi',
                                'Tipe User / Nama Prodi', 'Jumlah Kunjungan'
                            ];
                            csv.push(headers.join(delimiter));

                            result.data.forEach(row => {
                                const rowData = [
                                    `"${row.tanggal_kunjungan.replace(/"/g, '""')}"`,
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

                            let fileName = `laporan_kunjungan_prodi_${tanggalAwal}_${tanggalAkhir}`;
                            if (prodiFilter) {
                                fileName += `_${prodiFilter}`;
                            }
                            fileName += '.csv';


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
        });
    </script>
@endsection
