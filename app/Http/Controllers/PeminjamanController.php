<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    public function getBerlangsungExportData(Request $request)
    {
        $selectedProdiCode = $request->input('prodi', '');
        $listProdi = DB::connection('mysql2')->table('authorised_values')
            ->select('authorised_value', 'lib')
            ->where('category', 'PRODI')
            ->whereRaw('CHAR_LENGTH(lib) >= 13')
            ->orderBy('lib', 'asc')
            ->get();

        $namaProdiFilter = 'Semua Program Studi';
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

        $data = $query->get();

        $exportData = $data->map(function ($row) {
            return [
                'BukuDipinjamSaat' => Carbon::parse($row->BukuDipinjamSaat)->format('d M Y H:i:s'),
                'JudulBuku' => $row->JudulBuku,
                'BarcodeBuku' => $row->BarcodeBuku,
                'KodeProdi' => $row->KodeProdi,
                'Peminjam' => $row->Peminjam,
                'BatasWaktuPengembalian' => Carbon::parse($row->BatasWaktuPengembalian)->format('d M Y'),
            ];
        });

        return response()->json([
            'data' => $exportData,
            'namaProdiFilter' => $namaProdiFilter,
        ]);
    }

    public function pertanggal(Request $request)
    {
        $filterType = $request->input('filter_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $selectedYear = $request->input('selected_year');

        $statistics = collect();
        $fullStatistics = collect();
        $chartLabels = collect();
        $chartDataBooks = collect();
        $chartDataBorrowers = collect();

        if ($request->has('start_date') || $request->has('end_date') || $request->has('selected_year')) {
            try {
                if ($filterType == 'daily') {
                    // Logika untuk filter harian
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

                    // Query data
                    $fullStatistics = DB::connection('mysql2')->table('statistics as s')
                        ->select(
                            DB::raw('DATE(s.datetime) as tanggal'),
                            DB::raw('COUNT(s.itemnumber) as jumlah_peminjaman_buku'),
                            DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
                        )
                        ->whereIn('s.type', ['issue', 'renew'])
                        ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate])
                        ->groupBy(DB::raw('DATE(s.datetime)'))
                        ->orderBy(DB::raw('DATE(s.datetime)'), 'asc') // Urutkan ASC untuk chart
                        ->get();

                    // Query untuk tabel dengan pagination
                    $statistics = DB::connection('mysql2')->table('statistics as s')
                        ->select(
                            DB::raw('DATE(s.datetime) as tanggal'),
                            DB::raw('COUNT(s.itemnumber) as jumlah_peminjaman_buku'),
                            DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
                        )
                        ->whereIn('s.type', ['issue', 'renew'])
                        ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate])
                        ->groupBy(DB::raw('DATE(s.datetime)'))
                        ->orderBy(DB::raw('DATE(s.datetime)'), 'asc')
                        ->paginate(10);

                    $allDetailedStatistics = DB::connection('mysql2')->table('statistics as s')
                        ->select(
                            DB::raw('DATE(s.datetime) as tanggal'),
                            's.itemnumber',
                            'i.barcode',
                            'b.surname as nama_peminjam',
                            'bb.title as judul_buku',
                            'b.cardnumber'
                        )
                        ->join('borrowers as b', 's.borrowernumber', '=', 'b.borrowernumber')
                        ->join('items as i', 's.itemnumber', '=', 'i.itemnumber')
                        ->join('biblio as bb', 'i.biblionumber', '=', 'bb.biblionumber')
                        ->whereIn('s.type', ['issue', 'renew'])
                        ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate])
                        ->orderBy(DB::raw('DATE(s.datetime)'), 'asc')
                        ->orderBy('b.surname', 'ASC')
                        ->get();

                    // Tambahkan data detail ke dalam data ringkasan yang dipaginasi
                    $statistics->getCollection()->each(function ($stat) use ($allDetailedStatistics) {
                        $stat->details = $allDetailedStatistics->where('tanggal', $stat->tanggal)->values();
                    });

                    $chartLabels = $fullStatistics->pluck('tanggal')->map(function ($date) {
                        return Carbon::parse($date)->format('d M Y');
                    });
                } elseif ($filterType == 'monthly') {
                    // Logika untuk filter bulanan
                    if (empty($selectedYear) || !is_numeric($selectedYear) || $selectedYear < 1900 || $selectedYear > Carbon::now()->year + 1) {
                        $selectedYear = Carbon::now()->year;
                    }

                    // Query data bulanan
                    $fullStatistics = DB::connection('mysql2')->table('statistics as s')
                        ->select(
                            DB::raw('DATE_FORMAT(s.datetime, "%Y-%m") as periode'),
                            DB::raw('COUNT(s.itemnumber) as jumlah_peminjaman_buku'),
                            DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
                        )
                        ->whereIn('s.type', ['issue', 'renew'])
                        ->whereYear('s.datetime', $selectedYear)
                        ->groupBy(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'))
                        ->orderBy(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'), 'ASC')
                        ->get();

                    // Karena data bulanan tidak banyak, kita tidak perlu paginasi.
                    $statistics = $fullStatistics;

                    $allDetailedStatistics = DB::connection('mysql2')->table('statistics as s')
                        ->select(
                            DB::raw('DATE_FORMAT(s.datetime, "%Y-%m") as periode'),
                            's.itemnumber',
                            'i.barcode',
                            'b.surname as nama_peminjam',
                            'bb.title as judul_buku',
                            'b.cardnumber'
                        )
                        ->join('borrowers as b', 's.borrowernumber', '=', 'b.borrowernumber')
                        ->join('items as i', 's.itemnumber', '=', 'i.itemnumber')
                        ->join('biblio as bb', 'i.biblionumber', '=', 'bb.biblionumber')
                        ->whereIn('s.type', ['issue', 'renew'])
                        ->whereYear('s.datetime', $selectedYear)
                        ->get();

                    $statistics->each(function ($stat) use ($allDetailedStatistics) {
                        $stat->details = $allDetailedStatistics->where('periode', $stat->periode)->values();
                    });

                    $chartLabels = $fullStatistics->pluck('periode')->map(function ($periode) {
                        return Carbon::createFromFormat('Y-m', $periode)->format('M Y');
                    });
                }

                // Ambil data untuk chart dari fullStatistics (setelah ditarik dari database)
                $chartDataBooks = $fullStatistics->pluck('jumlah_peminjaman_buku');
                $chartDataBorrowers = $fullStatistics->pluck('jumlah_peminjam_unik');
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data statistik peminjaman: ' . $e->getMessage());
            }
        }

        // Kirimkan semua variabel ke view, baik ada isinya maupun kosong
        return view('pages.peminjaman.peminjamanRentangTanggal', compact(
            'statistics',
            'fullStatistics',
            'startDate',
            'endDate',
            'selectedYear',
            'filterType',
            'chartLabels',
            'chartDataBooks',
            'chartDataBorrowers',
            'request' // Penting: kirim objek request ke view agar bisa digunakan di Blade
        ));
    }

    // public function peminjamanProdiChart(Request $request)
    // {
    //     // Mengambil opsi program studi dari authorized_value
    //     $prodiOptions = DB::connection('mysql2')->table('authorised_values')
    //         ->select('authorised_value', 'lib')
    //         ->where('category', 'PRODI')
    //         ->whereRaw('CHAR_LENGTH(lib) >= 13')
    //         ->orderBy('lib', 'asc')
    //         ->get()
    //         ->map(function ($prodi) {
    //             $cleanedLib = $prodi->lib;
    //             if (str_starts_with($cleanedLib, 'FAI/ ')) {
    //                 $cleanedLib = substr($cleanedLib, 5);
    //             }
    //             $prodi->lib = trim($cleanedLib);
    //             return $prodi;
    //         });

    //     // Inisialisasi nilai filter default
    //     $filterType = $request->input('filter_type', 'yearly');
    //     $selectedYear = $request->input('selected_year', Carbon::now()->year);
    //     $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
    //     $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
    //     $selectedProdiCode = $request->input('selected_prodi', $prodiOptions->first()->authorised_value ?? null);

    //     $perPage = 10;
    //     $page = $request->input('page', 1);

    //     $statistics = collect();
    //     $allStatistics = collect();
    //     $chartLabels = collect();
    //     $chartDatasets = [];
    //     $dataExists = false;

    //     // Variabel baru untuk menampung total
    //     $totalBooks = 0;
    //     $totalBorrowers = 0;

    //     try {
    //         // Base query untuk mengambil data
    //         $baseQuery = DB::connection('mysql2')->table('statistics as s')
    //             ->leftJoin('borrowers as b', 'b.borrowernumber', '=', 's.borrowernumber')
    //             ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
    //             ->leftJoin('authorised_values as av', function ($join) {
    //                 $join->on('av.authorised_value', '=', 'ba.attribute')
    //                     ->where('av.category', '=', 'PRODI')
    //                     ->where('ba.code', '=', 'PRODI');
    //             })
    //             ->whereIn('s.type', ['issue', 'renew'])
    //             ->where('av.authorised_value', $selectedProdiCode);

    //         // Menggunakan satu base query untuk semua data
    //         $queryForTotals = clone $baseQuery;

    //         // Kondisi filter berdasarkan tipe (harian atau tahunan)
    //         if ($filterType == 'daily') {
    //             if (Carbon::parse($startDate)->greaterThan(Carbon::parse($endDate))) {
    //                 $temp = $startDate;
    //                 $startDate = $endDate;
    //                 $endDate = $temp;
    //             }

    //             $queryForBoth = (clone $baseQuery)
    //                 ->select(
    //                     DB::raw('DATE(s.datetime) as periode'),
    //                     DB::raw('COUNT(s.itemnumber) as jumlah_buku_terpinjam'),
    //                     DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
    //                 )
    //                 ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate])
    //                 ->groupBy(DB::raw('DATE(s.datetime)'))
    //                 ->orderBy(DB::raw('DATE(s.datetime)'), 'ASC');

    //             // Query untuk mendapatkan total dari semua data
    //             $queryForTotals->select(
    //                 DB::raw('COUNT(s.itemnumber) as total_buku'),
    //                 DB::raw('COUNT(DISTINCT s.borrowernumber) as total_peminjam')
    //             )
    //                 ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate]);

    //             $statistics = (clone $queryForBoth)->paginate($perPage, ['*'], 'page', $page);
    //             $allStatistics = (clone $queryForBoth)->get(); // Ambil semua data untuk export
    //             $chartLabels = $allStatistics->pluck('periode')->map(fn($date) => Carbon::parse($date)->format('d M Y'));
    //         } elseif ($filterType == 'yearly') {
    //             $queryForBoth = (clone $baseQuery)
    //                 ->select(
    //                     DB::raw('DATE_FORMAT(s.datetime, "%Y-%m") as periode'),
    //                     DB::raw('COUNT(s.itemnumber) as jumlah_buku_terpinjam'),
    //                     DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik')
    //                 )
    //                 ->whereYear('s.datetime', $selectedYear)
    //                 ->groupBy(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'))
    //                 ->orderBy(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'), 'ASC');

    //             // Query untuk mendapatkan total dari semua data
    //             $queryForTotals->select(
    //                 DB::raw('COUNT(s.itemnumber) as total_buku'),
    //                 DB::raw('COUNT(DISTINCT s.borrowernumber) as total_peminjam')
    //             )
    //                 ->whereYear('s.datetime', $selectedYear);

    //             $statistics = (clone $queryForBoth)->paginate($perPage, ['*'], 'page', $page);
    //             $allStatistics = (clone $queryForBoth)->get(); // Ambil semua data untuk export
    //             $chartLabels = $allStatistics->pluck('periode')->map(fn($periode) => Carbon::createFromFormat('Y-m', $periode)->format('M Y'));
    //         }

    //         // Memastikan data ada sebelum memproses
    //         if ($allStatistics->isNotEmpty()) {
    //             $dataExists = true;

    //             $totals = $queryForTotals->first();
    //             $totalBooks = $totals->total_buku;
    //             $totalBorrowers = $totals->total_peminjam;

    //             $chartDatasets[] = [
    //                 'label' => 'Jumlah Buku Terpinjam',
    //                 'data' => $allStatistics->pluck('jumlah_buku_terpinjam'),
    //                 'borderColor' => 'rgba(75, 192, 192, 1)',
    //                 'backgroundColor' => 'rgba(75, 192, 192, 0.4)',
    //                 'tension' => 0.1,
    //                 'fill' => true,
    //             ];

    //             $chartDatasets[] = [
    //                 'label' => 'Jumlah Peminjam',
    //                 'data' => $allStatistics->pluck('jumlah_peminjam_unik'),
    //                 'borderColor' => 'rgba(153, 102, 255, 1)',
    //                 'backgroundColor' => 'rgba(153, 102, 255, 0.4)',
    //                 'tension' => 0.1,
    //                 'fill' => true,
    //             ];
    //         }
    //     } catch (\Exception $e) {
    //         return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data statistik: ' . $e->getMessage());
    //     }

    //     // Mengirimkan semua data yang diperlukan ke view
    //     return view('pages.peminjaman.prodiChart', [
    //         'prodiOptions' => $prodiOptions,
    //         'selectedYear' => $selectedYear,
    //         'selectedProdiCode' => $selectedProdiCode,
    //         'filterType' => $filterType,
    //         'startDate' => $startDate,
    //         'endDate' => $endDate,
    //         'statistics' => $statistics,
    //         'allStatistics' => $allStatistics,
    //         'chartLabels' => $chartLabels,
    //         'chartDatasets' => $chartDatasets,
    //         'dataExists' => $dataExists,
    //         'totalBooks' => $totalBooks,
    //         'totalBorrowers' => $totalBorrowers
    //     ]);
    // }


    // public function getPeminjamDetail(Request $request)
    // {
    //     $periode = $request->input('periode');
    //     $prodiCode = $request->input('prodi_code');
    //     $filterType = $request->input('filter_type');

    //     if (!$periode || !$prodiCode || !$filterType) {
    //         return response()->json(['success' => false, 'message' => 'Invalid parameters.'], 400);
    //     }

    //     $perPage = 5;
    //     $page = $request->input('page', 1);
    //     $offset = ($page - 1) * $perPage;

    //     try {
    //         $baseQuery = DB::connection('mysql2')->table('statistics as s')
    //             ->select(
    //                 'b.cardnumber',
    //                 DB::raw("CONCAT(b.firstname, ' ', b.surname) as nama_peminjam"),
    //                 'bi.title',
    //                 DB::raw('s.datetime as waktu_transaksi'),
    //                 DB::raw('s.type')
    //             )
    //             ->leftJoin('borrowers as b', 'b.borrowernumber', '=', 's.borrowernumber')
    //             ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
    //             ->leftJoin('authorised_values as av', function ($join) {
    //                 $join->on('av.authorised_value', '=', 'ba.attribute')
    //                     ->where('av.category', '=', 'PRODI')
    //                     ->where('ba.code', '=', 'PRODI');
    //             })
    //             ->leftJoin('items as i', 'i.itemnumber', '=', 's.itemnumber')
    //             ->leftJoin('biblio as bi', 'bi.biblionumber', '=', 'i.biblionumber')
    //             ->whereIn('s.type', ['issue', 'renew'])
    //             ->where('av.authorised_value', $prodiCode);

    //         if ($filterType === 'daily') {
    //             $baseQuery->whereDate('s.datetime', $periode);
    //         } elseif ($filterType === 'yearly') {
    //             $baseQuery->where(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'), $periode);
    //         } else {
    //             return response()->json(['success' => false, 'message' => 'Invalid filter type.'], 400);
    //         }

    //         $borrowerDetails = $baseQuery
    //             ->orderBy('s.datetime', 'desc')
    //             ->get();

    //         $groupedData = [];
    //         foreach ($borrowerDetails as $detail) {
    //             if (!isset($groupedData[$detail->cardnumber])) {
    //                 $groupedData[$detail->cardnumber] = [
    //                     'nama_peminjam' => $detail->nama_peminjam,
    //                     'cardnumber' => $detail->cardnumber,
    //                     'buku' => [],
    //                 ];
    //             }
    //             if ($detail->title) {
    //                 $groupedData[$detail->cardnumber]['buku'][] = [
    //                     'title' => $detail->title,
    //                     'waktu_transaksi' => Carbon::parse($detail->waktu_transaksi)->format('d M Y H:i:s'),
    //                     'transaksi' => $detail->type,
    //                 ];
    //             }
    //         }

    //         $finalData = array_values($groupedData);

    //         $paginatedData = array_slice($finalData, $offset, $perPage);
    //         $totalItems = count($finalData);
    //         $totalPages = ceil($totalItems / $perPage);

    //         return response()->json([
    //             'success' => true,
    //             'data' => $paginatedData,
    //             'totalItems' => $totalItems,
    //             'totalPages' => $totalPages,
    //             'currentPage' => $page,
    //             'perPage' => $perPage,
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json(['success' => false, 'message' => 'Failed to fetch data: ' . $e->getMessage()], 500);
    //     }
    // }


    public function peminjamanProdiChart(Request $request)
    {
        // Mengambil opsi program studi dari authorized_value
        $prodiOptions = DB::connection('mysql2')->table('authorised_values')
            ->select('authorised_value', 'lib')
            ->where('category', 'PRODI')
            ->whereRaw('CHAR_LENGTH(lib) >= 13')
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

        // Inisialisasi nilai filter default
        $filterType = $request->input('filter_type', 'yearly');
        $selectedYear = $request->input('selected_year', Carbon::now()->year);
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $selectedProdiCode = $request->input('selected_prodi', $prodiOptions->first()->authorised_value ?? null);

        $perPage = 10;
        $page = $request->input('page', 1);

        $statistics = collect();
        $allStatistics = collect();
        $chartLabels = collect();
        $chartDatasets = [];
        $dataExists = false;

        $totalBooks = 0;
        $totalBorrowers = 0;

        try {
            $baseQuery = DB::connection('mysql2')->table('statistics as s')
                ->leftJoin('borrowers as b', 'b.borrowernumber', '=', 's.borrowernumber')
                ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
                ->leftJoin('authorised_values as av', function ($join) {
                    $join->on('av.authorised_value', '=', 'ba.attribute')
                        ->where('av.category', '=', 'PRODI')
                        ->where('ba.code', '=', 'PRODI');
                })
                ->whereIn('s.type', ['issue', 'renew', 'return']) // Tambahkan 'return' di sini
                ->where('av.authorised_value', $selectedProdiCode);

            $queryForTotals = clone $baseQuery;

            if ($filterType == 'daily') {
                if (Carbon::parse($startDate)->greaterThan(Carbon::parse($endDate))) {
                    $temp = $startDate;
                    $startDate = $endDate;
                    $endDate = $temp;
                }

                $queryForBoth = (clone $baseQuery)
                    ->select(
                        DB::raw('DATE(s.datetime) as periode'),
                        DB::raw('COUNT(CASE WHEN s.type IN ("issue", "renew") THEN s.itemnumber ELSE NULL END) as jumlah_buku_terpinjam'),
                        DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik'),
                        DB::raw('COUNT(CASE WHEN s.type = "return" THEN s.itemnumber ELSE NULL END) as jumlah_buku_kembali') // Kolom baru
                    )
                    ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate])
                    ->groupBy(DB::raw('DATE(s.datetime)'))
                    ->orderBy(DB::raw('DATE(s.datetime)'), 'ASC');

                $queryForTotals->select(
                    DB::raw('COUNT(CASE WHEN s.type IN ("issue", "renew") THEN s.itemnumber ELSE NULL END) as total_buku'),
                    DB::raw('COUNT(DISTINCT s.borrowernumber) as total_peminjam'),
                    DB::raw('COUNT(CASE WHEN s.type = "return" THEN s.itemnumber ELSE NULL END) as total_kembali') // Kolom baru
                )
                    ->whereBetween(DB::raw('DATE(s.datetime)'), [$startDate, $endDate]);

                $statistics = (clone $queryForBoth)->paginate($perPage, ['*'], 'page', $page);
                $allStatistics = (clone $queryForBoth)->get();
                $chartLabels = $allStatistics->pluck('periode')->map(fn($date) => Carbon::parse($date)->format('d M Y'));
            } elseif ($filterType == 'yearly') {
                $queryForBoth = (clone $baseQuery)
                    ->select(
                        DB::raw('DATE_FORMAT(s.datetime, "%Y-%m") as periode'),
                        DB::raw('COUNT(CASE WHEN s.type IN ("issue", "renew") THEN s.itemnumber ELSE NULL END) as jumlah_buku_terpinjam'),
                        DB::raw('COUNT(DISTINCT s.borrowernumber) as jumlah_peminjam_unik'),
                        DB::raw('COUNT(CASE WHEN s.type = "return" THEN s.itemnumber ELSE NULL END) as jumlah_buku_kembali') // Kolom baru
                    )
                    ->whereYear('s.datetime', $selectedYear)
                    ->groupBy(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'))
                    ->orderBy(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'), 'ASC');

                $queryForTotals->select(
                    DB::raw('COUNT(CASE WHEN s.type IN ("issue", "renew") THEN s.itemnumber ELSE NULL END) as total_buku'),
                    DB::raw('COUNT(DISTINCT s.borrowernumber) as total_peminjam'),
                    DB::raw('COUNT(CASE WHEN s.type = "return" THEN s.itemnumber ELSE NULL END) as total_kembali') // Kolom baru
                )
                    ->whereYear('s.datetime', $selectedYear);

                $statistics = (clone $queryForBoth)->paginate($perPage, ['*'], 'page', $page);
                $allStatistics = (clone $queryForBoth)->get();
                $chartLabels = $allStatistics->pluck('periode')->map(fn($periode) => Carbon::createFromFormat('Y-m', $periode)->format('M Y'));
            }

            if ($allStatistics->isNotEmpty()) {
                $dataExists = true;

                $totals = $queryForTotals->first();
                $totalBooks = $totals->total_buku;
                $totalBorrowers = $totals->total_peminjam;
                $totalReturns = $totals->total_kembali; // Ambil total pengembalian

                $chartDatasets[] = [
                    'label' => 'Jumlah Buku Terpinjam',
                    'data' => $allStatistics->pluck('jumlah_buku_terpinjam'),
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.4)',
                    'tension' => 0.1,
                    'fill' => true,
                ];

                $chartDatasets[] = [
                    'label' => 'Jumlah Buku Dikembalikan',
                    'data' => $allStatistics->pluck('jumlah_buku_kembali'),
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.4)',
                    'tension' => 0.1,
                    'fill' => true,
                ];

                $chartDatasets[] = [
                    'label' => 'Jumlah Peminjam',
                    'data' => $allStatistics->pluck('jumlah_peminjam_unik'),
                    'borderColor' => 'rgba(153, 102, 255, 1)',
                    'backgroundColor' => 'rgba(153, 102, 255, 0.4)',
                    'tension' => 0.1,
                    'fill' => true,
                ];
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data statistik: ' . $e->getMessage());
        }

        return view('pages.peminjaman.prodiChart', [
            'prodiOptions' => $prodiOptions,
            'selectedYear' => $selectedYear,
            'selectedProdiCode' => $selectedProdiCode,
            'filterType' => $filterType,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'statistics' => $statistics,
            'allStatistics' => $allStatistics,
            'chartLabels' => $chartLabels,
            'chartDatasets' => $chartDatasets,
            'dataExists' => $dataExists,
            'totalBooks' => $totalBooks,
            'totalBorrowers' => $totalBorrowers,
            'totalReturns' => $totalReturns, // Kirim total pengembalian ke view
        ]);
    }

    // Fungsi ini sudah diperbarui untuk menampilkan setiap transaksi
    public function getPeminjamDetail(Request $request)
    {
        $periode = $request->input('periode');
        $prodiCode = $request->input('prodi_code');
        $filterType = $request->input('filter_type');

        if (!$periode || !$prodiCode || !$filterType) {
            return response()->json(['success' => false, 'message' => 'Invalid parameters.'], 400);
        }

        $perPage = 5;
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;

        try {
            $baseQuery = DB::connection('mysql2')->table('statistics as s')
                ->select(
                    'b.cardnumber',
                    DB::raw("CONCAT(b.firstname, ' ', b.surname) as nama_peminjam"),
                    'bi.title',
                    DB::raw('s.datetime as waktu_transaksi'),
                    DB::raw('s.type')
                )
                ->leftJoin('borrowers as b', 'b.borrowernumber', '=', 's.borrowernumber')
                ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
                ->leftJoin('authorised_values as av', function ($join) {
                    $join->on('av.authorised_value', '=', 'ba.attribute')
                        ->where('av.category', '=', 'PRODI')
                        ->where('ba.code', '=', 'PRODI');
                })
                ->leftJoin('items as i', 'i.itemnumber', '=', 's.itemnumber')
                ->leftJoin('biblio as bi', 'bi.biblionumber', '=', 'i.biblionumber')
                ->whereIn('s.type', ['issue', 'renew', 'return']) // Tambahkan 'return' di sini
                ->where('av.authorised_value', $prodiCode);

            if ($filterType === 'daily') {
                $baseQuery->whereDate('s.datetime', $periode);
            } elseif ($filterType === 'yearly') {
                $baseQuery->where(DB::raw('DATE_FORMAT(s.datetime, "%Y-%m")'), $periode);
            } else {
                return response()->json(['success' => false, 'message' => 'Invalid filter type.'], 400);
            }

            $borrowerDetails = $baseQuery
                ->orderBy('s.datetime', 'desc')
                ->get();

            $groupedData = [];
            foreach ($borrowerDetails as $detail) {
                if (!isset($groupedData[$detail->cardnumber])) {
                    $groupedData[$detail->cardnumber] = [
                        'nama_peminjam' => $detail->nama_peminjam,
                        'cardnumber' => $detail->cardnumber,
                        'buku' => [],
                    ];
                }
                if ($detail->title) {
                    $groupedData[$detail->cardnumber]['buku'][] = [
                        'title' => $detail->title,
                        'waktu_transaksi' => Carbon::parse($detail->waktu_transaksi)->format('d M Y H:i:s'),
                        'transaksi' => $detail->type,
                    ];
                }
            }

            $finalData = array_values($groupedData);

            $paginatedData = array_slice($finalData, $offset, $perPage);
            $totalItems = count($finalData);
            $totalPages = ceil($totalItems / $perPage);

            return response()->json([
                'success' => true,
                'data' => $paginatedData,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'perPage' => $perPage,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch data: ' . $e->getMessage()], 500);
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
            ->whereRaw('CHAR_LENGTH(lib) >= 13')
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

            $activeLoans = $query->paginate(10)->withQueryString();
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
}
