<!DOCTYPE html>
<html>

<head>
    <title>Laporan Kehadiran Anggota</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
        }

        .member-info,
        .table-container {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Kehadiran Anggota</h1>
        <p>Periode: {{ \Carbon\Carbon::createFromFormat('Ym', $dataKunjungan->first()->tahun_bulan)->format('M Y') }} -
            {{ \Carbon\Carbon::createFromFormat('Ym', $dataKunjungan->last()->tahun_bulan)->format('M Y') }}</p>
    </div>

    <div class="member-info">
        <h3>Informasi Anggota</h3>
        <p><strong>Nomor Kartu:</strong> {{ $fullBorrowerDetails->cardnumber }}</p>
        <p><strong>Nama:</strong> {{ $fullBorrowerDetails->firstname }} {{ $fullBorrowerDetails->surname }}</p>
        <p><strong>Email:</strong> {{ $fullBorrowerDetails->email }}</p>
        <p><strong>Telepon:</strong> {{ $fullBorrowerDetails->phone }}</p>
    </div>

    <div class="table-container">
        <h3>Detail Kunjungan</h3>
        <table>
            <thead>
                <tr>
                    <th>Bulan Tahun</th>
                    <th>Jumlah Kunjungan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dataKunjungan as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::createFromFormat('Ym', $row->tahun_bulan)->format('M Y') }}</td>
                        <td>{{ $row->jumlah_kunjungan }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="total">Total Keseluruhan Kunjungan: {{ $dataKunjungan->sum('jumlah_kunjungan') }}</p>
    </div>

</body>

</html>
