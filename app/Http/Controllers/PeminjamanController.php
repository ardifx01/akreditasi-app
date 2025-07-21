<?php

namespace App\Http\Controllers;

use App\Models\M_Auv;
use App\Models\M_borrowers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\M_statistics;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    public function pertanggal(Request $request)
    {
        $filterType = $request->input('filter_type', 'daily');

        $startDate = null;
        $endDate = null;
        $selectedYear = null;
        $statistics = collect();
        $chartLabels = collect();
        $chartDataBooks = collect();
        $chartDataBorrowers = collect();

        try {
            if ($filterType == 'daily') {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');

                if (empty($startDate) || !Carbon::parse($startDate)->isValid()) {
                    $startDate = Carbon::now()->subDays(30)->format('Y-m-d');
                }
                if (empty($endDate) || !Carbon::parse($endDate)->isValid()) {
                    $endDate = Carbon::now()->format('Y-m-d');
                }

                if (Carbon::parse($startDate)->greaterThan(Carbon::parse($endDate))) {
                    $temp = $startDate;
                    $startDate = $endDate;
                    $endDate = $temp;
                }

                $query = DB::connection('mysql2')->table('statistics as s')
                    ->select(
                        DB::raw('DATE(s.datetime) as tanggal'),
                        DB::raw('COUNT(s.itemnumber) as jumlah_peminjaman_buku'),
                        DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
                    )
                    ->whereIn('s.type', ['issue', 'renew'])
                    ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate])
                    ->groupBy(DB::raw('DATE(s.datetime)'))
                    ->orderBy(DB::raw('DATE(s.datetime)'), 'ASC');

                $statistics = $query->paginate(10)->withQueryString();

                $chartLabels = $statistics->pluck('tanggal')->map(function ($date) {
                    return Carbon::parse($date)->format('d M Y');
                });
            } elseif ($filterType == 'monthly') {
                $selectedYear = $request->input('selected_year');

                if (empty($selectedYear) || !is_numeric($selectedYear) || $selectedYear < 1900 || $selectedYear > Carbon::now()->year + 1) {
                    $selectedYear = Carbon::now()->year;
                }

                $query = DB::connection('mysql2')->table('statistics as s')
                    ->select(
                        DB::raw('YEAR(s.datetime) as year'),
                        DB::raw('MONTH(s.datetime) as month'),
                        DB::raw('COUNT(s.itemnumber) as jumlah_peminjaman_buku'),
                        DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
                    )
                    ->whereIn('s.type', ['issue', 'renew'])
                    ->whereYear('s.datetime', $selectedYear)
                    ->groupBy(DB::raw('YEAR(s.datetime)'), DB::raw('MONTH(s.datetime)'))
                    ->orderBy(DB::raw('YEAR(s.datetime)'), 'ASC')
                    ->orderBy(DB::raw('MONTH(s.datetime)'), 'ASC');

                $rawData = $query->get();

                $allMonthsData = collect();
                for ($i = 1; $i <= 12; $i++) {
                    $monthPeriod = Carbon::create($selectedYear, $i, 1)->format('Y-m');
                    $allMonthsData->put($monthPeriod, (object)[
                        'periode' => $monthPeriod,
                        'jumlah_peminjaman_buku' => 0,
                        'jumlah_peminjam_unik' => 0,
                    ]);
                }

                foreach ($rawData as $stat) {
                    $periodeKey = Carbon::create($stat->year, $stat->month, 1)->format('Y-m');
                    if ($allMonthsData->has($periodeKey)) {
                        $obj = $allMonthsData->get($periodeKey);
                        $obj->jumlah_peminjaman_buku = $stat->jumlah_peminjaman_buku;
                        $obj->jumlah_peminjam_unik = $stat->jumlah_peminjam_unik;
                    }
                }

                $statistics = $allMonthsData->values();

                $chartLabels = $statistics->pluck('periode')->map(function ($periode) {
                    return Carbon::createFromFormat('Y-m', $periode)->format('M Y');
                });
            }

            $chartDataBooks = $statistics->pluck('jumlah_peminjaman_buku');
            $chartDataBorrowers = $statistics->pluck('jumlah_peminjam_unik');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data statistik peminjaman: ' . $e->getMessage());
        }

        return view('pages.peminjaman.peminjamanRentangTanggal', [
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedYear' => $selectedYear,
            'filterType' => $filterType,
            'chartLabels' => $chartLabels,
            'chartDataBooks' => $chartDataBooks,
            'chartDataBorrowers' => $chartDataBorrowers,
        ]);
    }

    public function peminjamanProdiChart(Request $request)
    {
        // Mengambil prodiOptions langsung dari database
        $prodiOptions = DB::connection('mysql2')->table('authorised_values')
            ->select('authorised_value', 'lib')
            ->where('category', 'PRODI')
            ->orderBy('lib', 'asc')
            ->get()
            ->map(function ($prodi) {
                $cleanedLib = $prodi->lib;
                if (str_starts_with($cleanedLib, 'FAI/ ')) {
                    $cleanedLib = substr($cleanedLib, 5);
                }
                $prodi->lib = trim($cleanedLib);
                return $prodi;
            });

        $selectedYear = $request->input('selected_year', Carbon::now()->year);
        $selectedProdiCodes = $request->input('selected_prodi', []);

        if (empty($selectedProdiCodes)) {
            $selectedProdiCodes = $prodiOptions->pluck('authorised_value')->toArray();
        }

        $statistics = collect();
        $chartLabels = collect();
        $chartDatasets = [];
        $dataExists = false;

        try {
            $query = DB::connection('mysql2')->table('statistics as s')
                ->select(
                    DB::raw('EXTRACT(YEAR_MONTH FROM s.datetime) as periode_ym'),
                    DB::raw('av.authorised_value as prodi_code'),
                    DB::raw('av.lib as prodi_name'),
                    DB::raw('COUNT(s.itemnumber) as jumlah_buku_terpinjam'),
                    DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
                )
                ->leftJoin('borrowers as b', 'b.borrowernumber', '=', 's.borrowernumber')
                ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
                ->leftJoin('authorised_values as av', function ($join) {
                    $join->on('av.authorised_value', '=', 'ba.attribute')
                        ->where('av.category', '=', 'PRODI')
                        ->where('ba.code', '=', 'PRODI');
                })
                ->whereIn('s.type', ['issue', 'renew']) // PENTING: KONDISI INI
                ->whereYear('s.datetime', $selectedYear);

            if (!empty($selectedProdiCodes)) {
                $query->whereIn('av.authorised_value', $selectedProdiCodes);
            }

            $query->groupBy(
                DB::raw('EXTRACT(YEAR_MONTH FROM s.datetime)'),
                DB::raw('av.authorised_value'),
                DB::raw('av.lib')
            )
                ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM s.datetime)'), 'ASC')
                ->orderBy(DB::raw('av.authorised_value'), 'ASC');

            $rawData = $query->get();

            if ($rawData->isNotEmpty()) {
                $dataExists = true;

                $statistics = $rawData->map(function ($item) {
                    $period = substr($item->periode_ym, 0, 4) . '-' . substr($item->periode_ym, 4, 2);
                    $cleanedProdiName = $item->prodi_name;
                    if (str_starts_with($cleanedProdiName, 'FAI/ ')) {
                        $cleanedProdiName = substr($cleanedProdiName, 5);
                    }
                    $cleanedProdiName = trim($cleanedProdiName);
                    return (object)[
                        'periode' => $period,
                        'prodi_code' => $item->prodi_code,
                        'prodi_name' => $cleanedProdiName,
                        'jumlah_buku_terpinjam' => $item->jumlah_buku_terpinjam,
                        'jumlah_peminjam_unik' => $item->jumlah_peminjam_unik,
                    ];
                });

                $uniqueMonths = $rawData->pluck('periode_ym')->unique()->sort()->values();
                $chartLabels = $uniqueMonths->map(function ($ym) {
                    return Carbon::createFromFormat('Ym', $ym)->format('M Y');
                });

                $actualProdiInResult = $rawData->pluck('prodi_code', 'prodi_name')->unique();

                $datasetsBooks = [];
                $datasetsBorrowers = [];

                $prodiBaseColors = [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCD56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#808080',
                    '#00FF00',
                    '#8A2BE2',
                    '#DC143C',
                ];
                $colorIndex = 0;

                foreach ($actualProdiInResult as $rawProdiName => $prodiCode) {
                    $cleanedProdiName = $rawProdiName;
                    if (str_starts_with($cleanedProdiName, 'FAI/ ')) {
                        $cleanedProdiName = substr($cleanedProdiName, 5);
                    }
                    $cleanedProdiName = trim($cleanedProdiName);

                    $baseColor = $prodiBaseColors[$colorIndex % count($prodiBaseColors)];
                    $colorIndex++;

                    list($r, $g, $b) = sscanf($baseColor, "#%02x%02x%02x");

                    $bookBorderColor = "rgb($r, $g, $b)";
                    $bookBackgroundColor = "rgba($r, $g, $b, 0.4)";

                    $borrowerBorderColor = "rgb(" . max(0, $r - 50) . ", " . max(0, $g - 50) . ", " . max(0, $b - 50) . ")"; // Sedikit lebih gelap
                    $borrowerBackgroundColor = "rgba(" . max(0, $r - 50) . ", " . max(0, $g - 70) . ", " . max(0, $b - 50) . ", 0.6)";

                    $datasetsBooks[$prodiCode] = [
                        'label' => 'Buku Terpinjam (' . $cleanedProdiName . ')',
                        'data' => array_fill(0, $uniqueMonths->count(), 0),
                        'borderColor' => $bookBorderColor,
                        'backgroundColor' => $bookBackgroundColor,
                        'tension' => 0.1,
                        'fill' => false,
                    ];
                    $datasetsBorrowers[$prodiCode] = [
                        'label' => 'Peminjam (' . $cleanedProdiName . ')',
                        'data' => array_fill(0, $uniqueMonths->count(), 0),
                        'borderColor' => $borrowerBorderColor,
                        'backgroundColor' => $borrowerBackgroundColor,
                        'tension' => 0.1,
                        'fill' => false,
                        'hidden' => true,
                    ];
                }

                foreach ($rawData as $item) {
                    $monthIndex = $uniqueMonths->search($item->periode_ym);
                    if ($monthIndex !== false && isset($datasetsBooks[$item->prodi_code])) {
                        $datasetsBooks[$item->prodi_code]['data'][$monthIndex] = $item->jumlah_buku_terpinjam;
                        $datasetsBorrowers[$item->prodi_code]['data'][$monthIndex] = $item->jumlah_peminjam_unik;
                    }
                }

                $chartDatasets = array_merge(array_values($datasetsBooks), array_values($datasetsBorrowers));
            } else {
                $chartLabels = collect();
                $chartDatasets = [];
            }
        } catch (\Exception $e) {
            // \Log::error('Error fetching per prodi statistics: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data statistik per prodi: ' . $e->getMessage());
        }

        return view('pages.peminjaman.prodiChart', [
            'prodiOptions' => $prodiOptions,
            'selectedYear' => $selectedYear,
            'selectedProdiCodes' => $selectedProdiCodes,
            'statistics' => $statistics,
            'chartLabels' => $chartLabels,
            'chartDatasets' => $chartDatasets,
            'dataExists' => $dataExists,
        ]);
    }

    public function getPeminjamDetail(Request $request)
    {
        $periodeYm = $request->input('periode_ym');
        $prodiCode = $request->input('prodi_code');

        if (!$periodeYm || !$prodiCode) {
            return response()->json(['error' => 'Periode atau Kode Prodi tidak lengkap.'], 400);
        }

        try {
            $year = substr($periodeYm, 0, 4);
            $month = substr($periodeYm, 4, 2);

            $peminjamList = DB::connection('mysql2')->table('statistics as s')
                ->select(DB::raw("TRIM(CONCAT(COALESCE(b.firstname, ''), ' ', COALESCE(b.surname, ''))) as nama_peminjam"))
                ->join('borrowers as b', 's.borrowernumber', '=', 'b.borrowernumber')
                ->leftJoin('borrower_attributes as ba', 'b.borrowernumber', '=', 'ba.borrowernumber')
                ->leftJoin('authorised_values as av', function ($join) {
                    $join->on('av.authorised_value', '=', 'ba.attribute')
                        ->where('av.category', '=', 'PRODI')
                        ->where('ba.code', '=', 'PRODI');
                })
                ->whereYear('s.datetime', $year)
                ->whereMonth('s.datetime', $month)
                ->whereIn('s.type', ['issue', 'renew'])
                ->where('av.authorised_value', $prodiCode)
                ->distinct()
                ->orderBy('nama_peminjam')
                ->get();

            if ($peminjamList->isEmpty()) {
                return response()->json(['message' => 'Tidak ada data peminjam ditemukan untuk periode dan prodi ini.', 'data' => []]);
            }

            return response()->json(['success' => true, 'data' => $peminjamList->pluck('nama_peminjam')]);
        } catch (\Exception $e) {
            // \Log::error('Error fetching peminjam detail: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            // return response()->json(['error' => 'Terjadi kesalahan saat mengambil detail peminjam.'], 500);
        }
    }

    public function checkHistory(Request $request)
    {
        $cardnumber = $request->input('cardnumber');
        $borrower = null;
        $borrowingHistory = collect();
        $returnHistory = collect();
        $errorMessage = null;

        if ($cardnumber) {
            try {
                $borrower = DB::connection('mysql2')->table('borrowers')
                    ->select('borrowernumber', 'cardnumber', 'firstname', 'surname', 'email', 'phone')
                    ->where('cardnumber', $cardnumber)
                    ->first();

                if ($borrower) {
                    $borrowingHistory = DB::connection('mysql2')->table('statistics as s')
                        ->select(
                            's.datetime',
                            's.itemnumber',
                            's.type', // issue, renew
                            'i.barcode',
                            'b.title',
                            'b.author'
                        )
                        ->leftJoin('items as i', 'i.itemnumber', '=', 's.itemnumber')
                        ->leftJoin('biblioitems as bi', 'bi.biblionumber', '=', 'i.biblionumber')
                        ->leftJoin('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                        ->where('s.borrowernumber', $borrower->borrowernumber)
                        ->whereIn('s.type', ['issue', 'renew'])
                        ->orderBy('s.datetime', 'desc')
                        ->paginate(10)
                        ->withQueryString();

                    // Histori Pengembalian (Return)
                    $returnHistory = DB::connection('mysql2')->table('statistics as s')
                        ->select(
                            's.datetime',
                            's.itemnumber',
                            's.type', // return
                            'i.barcode',
                            'b.title',
                            'b.author'
                        )
                        ->leftJoin('items as i', 'i.itemnumber', '=', 's.itemnumber')
                        ->leftJoin('biblioitems as bi', 'bi.biblionumber', '=', 'i.biblionumber')
                        ->leftJoin('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                        ->where('s.borrowernumber', $borrower->borrowernumber)
                        ->where('s.type', 'return')
                        ->orderBy('s.datetime', 'desc')
                        ->paginate(10)
                        ->withQueryString();
                } else {
                    $errorMessage = "Nomor kartu peminjam tidak ditemukan.";
                }
            } catch (\Exception $e) {
                // \Log::error('Error checking borrowing history: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
                $errorMessage = "Terjadi kesalahan saat mengambil histori peminjaman: " . $e->getMessage();
            }
        }

        return view('pages.peminjaman.cekPeminjaman', [
            'cardnumber' => $cardnumber,
            'borrower' => $borrower,
            'borrowingHistory' => $borrowingHistory,
            'returnHistory' => $returnHistory,
            'errorMessage' => $errorMessage,
        ]);
    }

    public function getBorrowingHistoryExportData(Request $request)
    {
        $cardnumber = $request->input('cardnumber');

        if (!$cardnumber) {
            return response()->json(['error' => 'Nomor Kartu Anggota (Cardnumber) diperlukan.'], 400);
        }

        $borrower = DB::connection('mysql2')->table('borrowers')
            ->select('borrowernumber', 'cardnumber', 'firstname', 'surname')
            ->where('cardnumber', $cardnumber)
            ->first();

        if (!$borrower) {
            return response()->json(['error' => 'Nomor kartu peminjam tidak ditemukan.'], 404);
        }
        $borrowingHistory = DB::connection('mysql2')->table('statistics as s')
            ->select(
                's.datetime',
                's.type', // issue, renew
                'i.barcode',
                'b.title',
                'b.author'
            )
            ->leftJoin('items as i', 'i.itemnumber', '=', 's.itemnumber')
            ->leftJoin('biblioitems as bi', 'bi.biblionumber', '=', 'i.biblionumber')
            ->leftJoin('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
            ->where('s.borrowernumber', $borrower->borrowernumber)
            ->whereIn('s.type', ['issue', 'renew'])
            ->orderBy('s.datetime', 'desc')
            ->get();

        $exportData = $borrowingHistory->map(function ($history) {
            return [
                'tanggal_waktu' => Carbon::parse($history->datetime)->format('d M Y H:i:s'),
                'tipe' => ucfirst($history->type),
                'barcode_buku' => $history->barcode,
                'judul_buku' => $history->title,
                'pengarang' => $history->author,
            ];
        });

        return response()->json([
            'data' => $exportData,
            'cardnumber' => $cardnumber,
            'borrower_name' => $borrower->firstname . ' ' . $borrower->surname,
            'type' => 'peminjaman'
        ]);
    }

    public function getReturnHistoryExportData(Request $request)
    {
        $cardnumber = $request->input('cardnumber');

        if (!$cardnumber) {
            return response()->json(['error' => 'Nomor Kartu Anggota (Cardnumber) diperlukan.'], 400);
        }

        $borrower = DB::connection('mysql2')->table('borrowers')
            ->select('borrowernumber', 'cardnumber', 'firstname', 'surname')
            ->where('cardnumber', $cardnumber)
            ->first();

        if (!$borrower) {
            return response()->json(['error' => 'Nomor kartu peminjam tidak ditemukan.'], 404);
        }

        $returnHistory = DB::connection('mysql2')->table('statistics as s')
            ->select(
                's.datetime',
                's.type', // return
                'i.barcode',
                'b.title',
                'b.author'
            )
            ->leftJoin('items as i', 'i.itemnumber', '=', 's.itemnumber')
            ->leftJoin('biblioitems as bi', 'bi.biblionumber', '=', 'i.biblionumber')
            ->leftJoin('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
            ->where('s.borrowernumber', $borrower->borrowernumber)
            ->where('s.type', 'return')
            ->orderBy('s.datetime', 'desc')
            ->get();

        $exportData = $returnHistory->map(function ($history) {
            return [
                'tanggal_waktu' => Carbon::parse($history->datetime)->format('d M Y H:i:s'),
                'tipe' => ucfirst($history->type),
                'barcode_buku' => $history->barcode,
                'judul_buku' => $history->title,
                'pengarang' => $history->author,
            ];
        });

        return response()->json([
            'data' => $exportData,
            'cardnumber' => $cardnumber,
            'borrower_name' => $borrower->firstname . ' ' . $borrower->surname,
            'type' => 'pengembalian'
        ]);
    }

    public function peminjamanBerlangsung(Request $request)
    {
        $listProdi = DB::connection('mysql2')->table('authorised_values')
            ->select('authorised_value', 'lib')
            ->where('category', 'PRODI')
            ->orderBy('lib', 'asc')
            ->get()
            ->map(function ($prodi) {
                $cleanedLib = $prodi->lib;
                if (str_starts_with($cleanedLib, 'FAI/ ')) {
                    $cleanedLib = substr($cleanedLib, 5);
                }
                $prodi->lib = trim($cleanedLib);
                return $prodi;
            });

        $selectedProdiCode = $request->input('prodi', '');

        $namaProdiFilter = 'Semua Program Studi';

        try {
            $query = DB::connection('mysql2')->table('issues as i')
                ->select(
                    'i.issuedate AS BukuDipinjamSaat',
                    'b.title AS JudulBuku',
                    'it.barcode AS BarcodeBuku',
                    'av.authorised_value AS KodeProdi',
                    DB::raw("CONCAT(
                        COALESCE(br.cardnumber, ''),
                        CASE WHEN br.cardnumber IS NOT NULL THEN ' - ' ELSE '' END,
                        TRIM(CONCAT(COALESCE(br.firstname, ''), ' ', COALESCE(br.surname, '')))
                    ) AS Peminjam"),
                    'i.date_due AS BatasWaktuPengembalian'
                )
                ->join('items as it', 'i.itemnumber', '=', 'it.itemnumber')
                ->join('biblio as b', 'it.biblionumber', '=', 'b.biblionumber')
                ->join('borrowers as br', 'i.borrowernumber', '=', 'br.borrowernumber')
                ->leftJoin('borrower_attributes as ba', 'br.borrowernumber', '=', 'ba.borrowernumber')
                ->leftJoin('authorised_values as av', function ($join) {
                    $join->on('av.category', '=', 'ba.code')
                        ->on('ba.attribute', '=', 'av.authorised_value');
                })
                ->whereRaw('i.date_due >= CURDATE()')
                ->orderBy('BukuDipinjamSaat', 'asc')
                ->orderBy('BatasWaktuPengembalian', 'asc');

            if ($selectedProdiCode) {
                $query->whereRaw('LEFT(br.cardnumber, 4) = ?', [$selectedProdiCode]);

                $foundProdi = $listProdi->firstWhere('authorised_value', $selectedProdiCode);
                if ($foundProdi) {
                    $namaProdiFilter = $foundProdi->lib;
                }
            }

            $activeLoans = $query->paginate(15)->withQueryString();
            $dataExists = $activeLoans->isNotEmpty();

            // Logika untuk export CSV
            if ($request->has('export_csv')) {
                $dataToExport = $query->get();
                return $this->exportCsvPeminjamanBerlangsung($dataToExport, $namaProdiFilter);
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            // \Log::error('Error fetching active loans: ' . $e->getMessage() . ' - ' . $e->getFile() . ':' . $e->getLine());
            // return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data peminjaman berlangsung: ' . $e->getMessage());
        }

        return view('pages.peminjaman.peminjamanBerlangsung', compact(
            'activeLoans',
            'listProdi',
            'selectedProdiCode',
            'namaProdiFilter',
            'dataExists'
        ));
    }

    private function exportCsvPeminjamanBerlangsung($data, $namaProdiFilter)
    {
        $filename = "peminjaman_berlangsung";
        if ($namaProdiFilter && $namaProdiFilter !== 'Semua Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdiFilter));
            $filename .= "_" . $cleanProdiName;
        }
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'Buku Dipinjam Saat',
            'Judul Buku',
            'Penulis',
            'Barcode Buku',
            'Kode Prodi',
            'Peminjam',
            'Batas Waktu Pengembalian'
        ];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');

            foreach ($data as $row) {
                fputcsv($file, [
                    Carbon::parse($row->BukuDipinjamSaat)->format('d M Y H:i:s'),
                    $row->JudulBuku,
                    $row->Penulis,
                    $row->BarcodeBuku,
                    $row->KodeProdi,
                    $row->Peminjam,
                    Carbon::parse($row->BatasWaktuPengembalian)->format('d M Y')
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
