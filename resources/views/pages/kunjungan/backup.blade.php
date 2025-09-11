<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran Kunjungan</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header td {
            vertical-align: middle;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
            text-align: center;
        }

        .sub {
            font-size: 11px;
            color: #333;
            text-align: center;
        }

        .meta,
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .meta td {
            padding: 6px 8px;
            border: 1px solid #bbb;
            vertical-align: top;
        }

        .meta .label {
            width: 28%;
            background: #f6f6f6;
            font-weight: bold;
        }

        .table th,
        .table td {
            border: 1px solid #bbb;
            padding: 6px 8px;
        }

        .table th {
            background: #efefef;
            font-weight: bold;
            text-align: center;
        }

        .table td.num {
            text-align: center;
            width: 48px;
        }

        .table td.right {
            text-align: right;
        }

        .footer {
            border-top: 1px solid #ccc;
            margin-top: 16px;
            padding-top: 8px;
            font-size: 11px;
            color: #666;
        }

        .table,
        .table tr,
        .table td,
        .table th {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>
    @php
        /**
         * ================== KONFIGURASI LOGO ==================
         * Ganti nama file di bawah kalau perlu. Default: public/img/logo0.png
         */
        $LOGO_FILENAME = 'logo21.png';

        /**
         * ================== UTIL: Ambil base64 dari public/img ==================
         * Selalu buat path absolut dari public_path('img/...'), aman di Windows/Linux.
         * Tidak akan mengeluarkan <img src=""> yang kosong.
         */
        function logo_base64_from_public_img($filename)
        {
            if (!is_string($filename) || $filename === '') {
                return null;
            }
            // normalisasi separator
            $filename = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filename);
            // jika user kasih "public\img\logo0.png" ubah ke "img\logo0.png"
            $filename = preg_replace('#^public' . preg_quote(DIRECTORY_SEPARATOR, '#') . '#i', '', $filename);
            // jika masih belum diawali "img", tambahkan
            if (stripos($filename, 'img' . DIRECTORY_SEPARATOR) !== 0) {
                $filename = 'img' . DIRECTORY_SEPARATOR . $filename;
            }
            $abs = public_path($filename);
            if (!file_exists($abs)) {
                return null;
            }

            $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
            $mimes = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'webp' => 'image/webp',
            ];
            $mime = $mimes[$ext] ?? 'image/png';
            $data = @file_get_contents($abs);
            if ($data === false) {
                return null;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($data);
        }

        // Ambil logo kiri & kanan (kalau mau sama, pakai satu saja)
        $logoLeftB64 = logo_base64_from_public_img($LOGO_FILENAME);
        $logoRightB64 = null; // atau: logo_base64_from_public_img($LOGO_FILENAME);

        /**
         * ================== UTIL: Format YEAR_MONTH -> "Januari 2025" ==================
         */
        function ym_to_text($ym)
        {
            $s = (string) $ym;
            if (strlen($s) === 5) {
                $year = substr($s, 0, 4);
                $month = '0' . substr($s, 4, 1);
            } else {
                $year = substr($s, 0, 4);
                $month = substr($s, 4, 2);
            }
            $bulan = [
                '',
                'Januari',
                'Februari',
                'Maret',
                'April',
                'Mei',
                'Juni',
                'Juli',
                'Agustus',
                'September',
                'Oktober',
                'November',
                'Desember',
            ];
            $m = (int) $month;
            return ($bulan[$m] ?? $month) . ' ' . $year;
        }

        // Tentukan periode dari data kunjungan
        $periodeText = 'Tidak ada data';
        if (isset($dataKunjungan) && count($dataKunjungan) > 0) {
            $firstYM = $dataKunjungan->first()->tahun_bulan ?? null;
            $lastYM = $dataKunjungan->last()->tahun_bulan ?? null;
            if ($firstYM && $lastYM) {
                $periodeText = ym_to_text($firstYM) . ' – ' . ym_to_text($lastYM);
            }
        }

        // Waktu cetak
        $printedAt = date('d/m/Y H:i');
    @endphp

    <!-- ================== HEADER ================== -->
    <table class="header">
        <tr>
            <td style="width:16%;">
                @if ($logoLeftB64)
                    <img src="{{ $logoLeftB64 }}" alt="Logo" style="height:56px;">
                @endif
            </td>
            <td style="width:68%;">
                <div class="title">Laporan Kehadiran Kunjungan Perpustakaan</div>
                <div class="sub">
                    Periode: {{ $periodeText }}<br>
                    Dicetak: {{ $printedAt }}
                </div>
            </td>
            <td style="width:16%; text-align:right;">
                @if ($logoRightB64)
                    <img src="{{ $logoRightB64 }}" alt="Logo" style="height:56px;">
                @endif
            </td>
        </tr>
    </table>

    <!-- ================== DATA ANGGOTA ================== -->
    <table class="meta">
        <tr>
            <td class="label">Nama</td>
            <td>{{ trim(($fullBorrowerDetails->firstname ?? '') . ' ' . ($fullBorrowerDetails->surname ?? '')) ?: '-' }}
            </td>
        </tr>
        <tr>
            <td class="label">Nomor Kartu Anggota</td>
            <td>{{ $fullBorrowerDetails->cardnumber ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Email</td>
            <td>{{ $fullBorrowerDetails->email ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Telepon</td>
            <td>{{ $fullBorrowerDetails->phone ?? '-' }}</td>
        </tr>
    </table>

    <!-- ================== TABEL KUNJUNGAN ================== -->
    <table class="table" style="margin-top:14px;">
        <thead>
            <tr>
                <th style="width:48px;">No</th>
                <th>Bulan</th>
                <th style="width:160px;">Jumlah Kunjungan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $total = 0;
            @endphp
            @forelse($dataKunjungan as $row)
                @php
                    $bulanText = ym_to_text($row->tahun_bulan ?? '');
                    $jml = (int) ($row->jumlah_kunjungan ?? 0);
                    $total += $jml;
                @endphp
                <tr>
                    <td class="num">{{ $no++ }}</td>
                    <td>{{ $bulanText }}</td>
                    <td class="right">{{ number_format($jml, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align:center; padding:14px;">Tidak ada data kunjungan untuk
                        ditampilkan.</td>
                </tr>
            @endforelse
            @if (($dataKunjungan ?? collect())->count() > 0)
                <tr>
                    <td colspan="2" class="right" style="font-weight:bold;">Total</td>
                    <td class="right" style="font-weight:bold;">{{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- ================== FOOTER ================== -->
    <div class="footer">
        * Laporan ini dihasilkan otomatis dari sistem kunjungan. Apabila terdapat perbedaan data,
        silakan hubungi petugas perpustakaan untuk verifikasi.
    </div>
</body>

</html>
