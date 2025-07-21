@extends('layouts.app')

@section('title', 'Peminjaman Berlangsung')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">Daftar Peminjaman Berlangsung @if ($selectedProdiCode)
                - {{ $namaProdiFilter }}
            @endif
        </h4>

        <form method="GET" action="{{ route('peminjaman.berlangsung') }}" class="row g-3 mb-4 align-items-end"
            id="filterPeminjamanBerlangsungForm">
            <div class="col-md-4">
                <label for="prodi" class="form-label">Filter Prodi</label>
                <select name="prodi" id="prodi" class="form-select">
                    <option value="">-- Semua Program Studi --</option>
                    @foreach ($listProdi as $prodi)
                        <option value="{{ $prodi->authorised_value }}"
                            {{ $selectedProdiCode == $prodi->authorised_value ? 'selected' : '' }}>
                            {{ $prodi->lib }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
            </div>
        </form>

        <div class="card shadow-sm">
            @if ($selectedProdiCode || $dataExists)
                <div class="card-header d-flex justify-content-between align-items-center">
                    Data Peminjaman Berlangsung
                    <button type="submit" form="filterPeminjamanBerlangsungForm" name="export_csv" value="1"
                        class="btn btn-success btn-sm">Export CSV</button>
                </div>
            @endif
            <div class="card-body">
                @if ($dataExists)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Buku Dipinjam Saat</th>
                                    <th>Judul Buku</th>
                                    <th>Barcode Buku</th>
                                    <th>Kode Prodi</th>
                                    <th>Peminjam</th>
                                    <th>Batas Waktu Pengembalian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($activeLoans as $index => $loan)
                                    <tr>
                                        <td>{{ $activeLoans->firstItem() + $index }}</td>
                                        <td>{{ \Carbon\Carbon::parse($loan->BukuDipinjamSaat)->format('d F Y H:i:s') }}</td>
                                        <td>{{ $loan->JudulBuku }}</td>
                                        <td>{{ $loan->BarcodeBuku }}</td>
                                        <td>{{ $loan->KodeProdi }}</td> {{-- Mengakses KodeProdi --}}
                                        <td>{{ $loan->Peminjam }}</td>
                                        <td>{{ \Carbon\Carbon::parse($loan->BatasWaktuPengembalian)->format('d F Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $activeLoans->links() }}
                    </div>
                @else
                    <div class="alert alert-info text-center" role="alert">
                        Tidak ada data peminjaman yang sedang berlangsung
                        @if ($selectedProdiCode)
                            untuk program studi {{ $namaProdiFilter }}
                        @endif
                        saat ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
