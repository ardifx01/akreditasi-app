@extends('layouts.app') {{-- Sesuaikan dengan layout aplikasi Anda --}}

@section('content')
@section('title', 'Cek Histori Peminjaman')
<div class="container">
    <h4>Cek Histori Peminjaman</h4>

    <div class="card mb-4">
        <div class="card-header">
            Cari Berdasarkan Nomor Kartu
        </div>
        <div class="card-body">
            <form action="{{ route('peminjaman.check_history') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="cardnumber" class="form-label">Nomor Kartu Peminjam:</label>
                    <input type="text" name="cardnumber" id="cardnumber" class="form-control"
                        value="{{ $cardnumber ?? '' }}" placeholder="Masukkan nomor kartu">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
        </div>
    </div>

    @if ($errorMessage)
        <div class="alert alert-danger">
            {{ $errorMessage }}
        </div>
    @endif

    @if ($borrower)
        <div class="card mb-4">
            <div class="card-header">
                Informasi Peminjam
            </div>
            <div class="card-body ">
                <p><strong>Nomor Kartu:</strong> {{ $borrower->cardnumber }}</p>
                <p><strong>Nama:</strong> {{ $borrower->firstname }} {{ $borrower->surname }}</p>
                <p><strong>Email:</strong> {{ $borrower->email }}</p>
                <p><strong>Telepon:</strong> {{ $borrower->phone }}</p>
            </div>
        </div>

        {{-- Histori Peminjaman --}}
        <div class="card mb-4">
            <div class="card-header">
                Histori Peminjaman (Issue & Renew)
            </div>
            <div class="card-body">
                @if ($borrowingHistory->isEmpty())
                    <div class="alert alert-info">Belum ada histori peminjaman atau perpanjangan untuk peminjam ini.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Tipe</th>
                                    <th>Barcode Buku</th>
                                    <th>Judul Buku</th>
                                    <th>Pengarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($borrowingHistory as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->datetime)->format('d M Y H:i:s') }}</td>
                                        <td>{{ ucfirst($history->type) }}</td>
                                        <td>{{ $history->barcode }}</td>
                                        <td>{{ $history->title }}</td>
                                        <td>{{ $history->author }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end">
                            {{ $borrowingHistory->links() }} {{-- Pagination links --}}
                        </div>
                    </div>
                @endif
            </div>

        </div>

        {{-- Histori Pengembalian --}}
        <div class="card">
            <div class="card-header">
                Histori Pengembalian (Return)
            </div>
            <div class="card-body">
                @if ($returnHistory->isEmpty())
                    <div class="alert alert-info">Belum ada histori pengembalian untuk peminjam ini.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Tipe</th>
                                    <th>Barcode Buku</th>
                                    <th>Judul Buku</th>
                                    <th>Pengarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($returnHistory as $history)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($history->datetime)->format('d M Y H:i:s') }}</td>
                                        <td>{{ ucfirst($history->type) }}</td>
                                        <td>{{ $history->barcode }}</td>
                                        <td>{{ $history->title }}</td>
                                        <td>{{ $history->author }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-end">
                            {{ $returnHistory->links() }} {{-- Pagination links --}}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @elseif ($cardnumber && !$errorMessage)
        {{-- Pesan ini hanya muncul jika cardnumber dimasukkan tapi peminjam tidak ditemukan --}}
        <div class="alert alert-warning">
            Nomor kartu peminjam "<strong>{{ $cardnumber }}</strong>" tidak ditemukan.
        </div>
    @endif
</div>
@endsection
