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

        .logo {
            display: block;
            margin: 0 auto 10px;
            width: 50px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0;
        }

        .header h2 {
            font-size: 18px;
            margin: 0;
        }

        .header p {
            margin: 0;
            font-size: 10px;
        }

        .line {
            border-top: 2px solid #000;
            margin: 10px 0;
        }

        .content {
            margin-top: 20px;
        }

        .member-info {
            margin-bottom: 20px;
        }

        .member-info h3 {
            font-size: 14px;
        }

        .member-info p {
            margin: 5px 0;
        }

        .member-info p strong {
            display: inline-block;
            width: 150px;
            /* Lebar yang seragam untuk label */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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

        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 12px;
        }

        .signature {
            margin-top: 50px;
            text-align: center;
        }

        .signature-line {
            margin-top: 5px;
            border-bottom: 1px solid #000;
            width: 150px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ asset('img/logo0.png') }}" alt="Logo UMS" class="logo">
        <h1>UPT PERPUSTAKAAN DAN LAYANAN DIGITAL</h1>
        <h2>UNIVERSITAS MUHAMMADIYAH SURAKARTA</h2>
        <p>Jl. A.Yani Tromol Pos I Pabelan Surakarta 57102 Telp.0271-717417</p>
        <p>web : library.ums.ac.id email : perpus@ums.ac.id / humas.libums@gmail.com</p>
        <div class="line"></div>
    </div>

    <div class="content">
        <div class="header" style="margin-bottom: 5px;">
            <h3>LAPORAN KEHADIRAN ANGGOTA</h3>
            <p>Periode:
                {{ \Carbon\Carbon::createFromFormat('Ym', $dataKunjungan->first()->tahun_bulan)->format('M Y') }} -
                {{ \Carbon\Carbon::createFromFormat('Ym', $dataKunjungan->last()->tahun_bulan)->format('M Y') }}</p>
        </div>

        <div class="member-info">
            <p><strong>Nomor Kartu Anggota</strong>: {{ $fullBorrowerDetails->cardnumber }}</p>
            <p><strong>Nama</strong>: {{ $fullBorrowerDetails->firstname }} {{ $fullBorrowerDetails->surname }}</p>
            <p><strong>Email</strong>: {{ $fullBorrowerDetails->email }}</p>
            <p><strong>Telepon</strong>: {{ $fullBorrowerDetails->phone }}</p>
        </div>

        <div class="table-container">
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
    </div>

    <div class="footer">
        <p>Surakarta, {{ date('d F Y') }}</p>
        <p style="margin-top: 5px;">Petugas</p>
        <div style="height: 50px;"></div>
        <p>(...............................)</p>
    </div>
</body>

</html>
