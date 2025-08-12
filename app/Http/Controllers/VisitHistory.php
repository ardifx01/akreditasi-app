<?php

namespace App\Http\Controllers;

use App\Models\M_eprodi;
use App\Models\M_vishistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VisitHistory extends Controller
{
    //  Data Mapping prodi
    private $prodiMapping = [
        'H000' => 'Ushuluddin',
        'G600' => 'FAI/ AKTA-IV (FAI)',
        'L200' => 'Teknik Informatika',
        'D100' => 'Teknik Sipil',
        'D400' => 'Teknik Elektro',
        'A210'    => 'Pend. Akuntansi',
        'A220'    => 'Pend. Pancasila dan Kewarganegaraan',
        'A310'    => 'Pend. Bahasa Indonesia',
        'A320'    => 'Pend. Bhs. Inggris',
        'A410'    => 'Pend. Matematika',
        'A420'    => 'Pend. Biologi',
        'A510'    => 'Pend. Guru SD',
        'A520'    => 'Pend. Guru Pend. Anak Usia Dini',
        'A610'    => 'Pendidikan Geografi',
        'A710'    => 'Pendidikan Teknik Informatika',
        'A810'    => 'Pendidikan Olahraga',
        'A900'    => 'Pendidikan Profesi Guru',
        'B100'    => 'Manajemen',
        'B10A'    => 'International Program Management',
        'B200'    => 'S1 Akuntansi',
        'B300'    => 'S1 Ekonomi Pembangunan',
        'C100'    => 'Hukum',
        'D100'    => 'Teknik Sipil',
        'D10A'    => 'Civil Engineering',
        'D200'    => 'Teknik Mesin',
        'D20A'    => 'Mechanical Engineering',
        'D300'    => 'Arsitektur',
        'D400'    => 'Teknik Elektro',
        'D500'    => 'Teknik Kimia',
        'D600'    => 'Teknik Industri',
        'E100'    => 'Geografi',
        'F100'    => 'Psikologi',
        'G000'    => 'S1 Pendidikan Agama Islam',
        'G100'    => 'S1 Ilmu Alquran dan Tafsir',
        'G108'    => 'S2 Ilmu Alquran dan Tafsir',
        'H100'    => 'Pondok Sobron',
        'I000'    => 'S1 Hukum Ekonomi Syariah',
        'J100'    => 'Fisioterapi (D3)',
        'J120'    => 'Fisioterapi S1',
        'J130'    => 'Profesi Fisioterapi',
        'J210'    => 'Ilmu Keperawatan (S1)',
        'J230'    => 'Keperawatan Profesi (NERS)',
        'J310'    => 'Ilmu Gizi (S1)',
        'J410'    => 'Kesehatan Masyarakat (S1)',
        'J500'    => 'Pendidikan Dokter',
        'J510'    => 'Profesi Dokter',
        'J520' => 'Pendidikan Dokter Gigi',
        'J530'    => 'Profesi Dokter Gigi',
        'K100'    => 'Farmasi',
        'K110'    => 'Profesi Apoteker',
        'L100'    => 'Ilmu Komunikasi',
        'L200'    => 'Informatika (Informatics)',
        'O000'    => 'Magister Studi Islam',
        'O100'    => 'S2 Pendidikan Agama Islam',
        'O200'    => 'S2 Hukum Ekonomi Syariah',
        'O300'    => 'S3 Pendidikan Agama Islam',
        'P100'    => 'S2 Manajemen',
        'Q100'    => 'Magister Administrasi Pendidikan',
        'Q200'    => 'Magister Pendidikan Dasar',
        'R100'    => 'Magister Ilmu Hukum',
        'R200'    => 'Ilmu Hukum',
        'S100'    => 'Magister Teknik Sipil',
        'S200'    => 'Magister Pengkajian Bahasa',
        'S300'    => 'Magister Psikologi',
        'T100'    => 'Magister Profesi Psikologi',
        'U100'    => 'Magister Teknik Mesin',
        'U200'    => 'Magister Teknik Kimia',
        'V100'    => 'Magister Farmasi',
        'W100'    => 'S2 Akuntansi',
        'B109'    => 'S3 Manajemen',
        'KI00'    => 'Profesi Apoteker Industri',
        'KR00'    => 'Profesi Apoteker Rumah Sakit',
    ];

    public function __construct()
    {
        $this->prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray();
        $this->prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray();
        $this->prodiMapping['DOSEN_TENDIK'] = 'Dosen / Tenaga Kependidikan';
    }
    // public function kunjunganProdiChart(Request $request)
    // {
    //     $listProdi = M_eprodi::pluck('nama', 'kode')->toArray();
    //     $prodi = $request->input('prodi');
    //     $thnDari = $request->input('tahun_awal');
    //     $thnSampai = $request->input('tahun_akhir');

    //     $data = collect();
    //     $namaProdi = 'Pilih Program Studi';
    //     $totalKeseluruhanKunjungan = 0; // Inisialisasi variabel total keseluruhan

    //     if ($prodi && $thnDari && $thnSampai) {
    //         $kodeProdiUntukFilter = ($prodi === 'all') ? array_keys($listProdi) : [$prodi];

    //         if ($prodi === 'all') {
    //             $namaProdi = 'Semua Prodi';
    //         } else {
    //             $namaProdi = $listProdi[$prodi] ?? 'Tidak Ditemukan';
    //         }

    //         $baseQuery = M_vishistory::selectRaw('
    //             EXTRACT(YEAR_MONTH FROM visitorhistory.visittime) as tahun_bulan,
    //             av.authorised_value as kode_prodi,
    //             le.nama as nama_prodi,
    //             COUNT(visitorhistory.id) as jumlah_kunjungan
    //         ')
    //             ->leftJoin('borrowers as b', 'visitorhistory.cardnumber', '=', 'b.cardnumber') // Perbaiki nama tabel
    //             ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
    //             ->leftJoin('authorised_values as av', function ($join) {
    //                 $join->on('ba.code', '=', 'av.category')
    //                     ->on('ba.attribute', '=', 'av.authorised_value');
    //             })
    //             ->leftJoin('local_eprodi as le', 'le.kode', '=', 'av.authorised_value')
    //             ->whereBetween(DB::raw('YEAR(visitorhistory.visittime)'), [(int)$thnDari, (int)$thnSampai]); // Perbaiki nama tabel

    //         // Jika bukan 'all', tambahkan filter prodi
    //         if ($prodi !== 'all') {
    //             $baseQuery->whereIn('av.authorised_value', $kodeProdiUntukFilter);
    //         }

    //         // Kloning query untuk mendapatkan total keseluruhan sebelum paginasi
    //         $totalQuery = clone $baseQuery; // Clone query
    //         $totalData = $totalQuery->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'av.authorised_value', 'le.nama')
    //             ->get(); // Ambil semua data tanpa paginasi untuk total
    //         $totalKeseluruhanKunjungan = $totalData->sum('jumlah_kunjungan'); // Hitung totalnya


    //         // Terapkan paginasi pada baseQuery
    //         $data = $baseQuery->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'av.authorised_value', 'le.nama')
    //             ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'asc')
    //             ->orderBy('av.authorised_value', 'asc')
    //             ->orderBy('le.nama', 'asc')
    //             ->paginate(12)
    //             ->withQueryString();
    //     }

    //     $selectedProdi = $request->input('prodi', '');
    //     $selectedTahunAwal = $request->input('tahun_awal', now()->year - 2);
    //     $selectedTahunAkhir = $request->input('tahun_akhir', now()->year);

    //     return view('pages.kunjungan.prodiChart', compact('data', 'listProdi', 'namaProdi', 'selectedProdi', 'selectedTahunAwal', 'selectedTahunAkhir', 'totalKeseluruhanKunjungan'));
    // }



    public function kunjunganProdiTable(Request $request)
    {
        $listProdi = M_eprodi::pluck('nama', 'kode')->toArray();
        $listProdi = ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'] + $listProdi;

        // Ambil parameter dari request
        $filterType = $request->input('filter_type', 'daily');
        $kodeProdiFilter = $request->input('prodi');

        // Tentukan rentang tanggal berdasarkan filter
        if ($filterType === 'yearly') {
            $selectedYear = $request->input('tahun', Carbon::now()->year);
            $tanggalAwal = Carbon::createFromDate($selectedYear, 1, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($selectedYear, 12, 31)->format('Y-m-d');
        } else { // 'daily'
            $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $selectedYear = null;
        }

        // Tentukan kolom dan pengelompokan berdasarkan filter
        if ($filterType === 'yearly') {
            $baseQuery = M_vishistory::selectRaw('
                DATE_FORMAT(visittime, "%Y-%m") as bulan,
                CASE
                    WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK"
                    ELSE SUBSTR(cardnumber, 1, 4)
                END as kode_identifikasi,
                COUNT(id) as jumlah_kunjungan
            ')
                ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
        } else {
            $baseQuery = M_vishistory::selectRaw('
                DATE(visittime) as tanggal_kunjungan,
                CASE
                    WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK"
                    ELSE SUBSTR(cardnumber, 1, 4)
                END as kode_identifikasi,
                COUNT(id) as jumlah_kunjungan
            ')
                ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
        }

        if (!empty($kodeProdiFilter)) {
            if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
                $baseQuery->whereRaw('LENGTH(cardnumber) <= 6');
            } else {
                $baseQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
            }
        }

        // Grouping dan total kunjungan
        $totalQuery = clone $baseQuery;

        if ($filterType === 'yearly') {
            $totalQuery->groupBy(
                DB::raw('DATE_FORMAT(visittime, "%Y-%m")'),
                DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END')
            );
        } else {
            $totalQuery->groupBy(
                DB::raw('DATE(visittime)'),
                DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END')
            );
        }

        $totalData = $totalQuery->get();
        $totalKeseluruhanKunjungan = $totalData->sum('jumlah_kunjungan');

        // Paginasi data utama
        if ($filterType === 'yearly') {
            $data = $baseQuery->groupBy(
                DB::raw('DATE_FORMAT(visittime, "%Y-%m")'),
                DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END')
            )
                ->orderBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m")'), 'asc')
                ->orderBy(DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END'), 'asc')
                ->paginate(10);
        } else {
            $data = $baseQuery->groupBy(
                DB::raw('DATE(visittime)'),
                DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END')
            )
                ->orderBy(DB::raw('DATE(visittime)'), 'asc')
                ->orderBy(DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END'), 'asc')
                ->paginate(10);
        }

        $prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray() + ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'];
        $data->getCollection()->transform(function ($item) use ($prodiMapping) {
            $item->nama_prodi = $prodiMapping[strtoupper($item->kode_identifikasi)] ?? 'Prodi Tidak Dikenal';
            $item->kode_prodi = $item->kode_identifikasi;
            return $item;
        });

        $data->appends($request->all());

        return view('pages.kunjungan.prodiTable', compact('data', 'listProdi', 'totalKeseluruhanKunjungan', 'tanggalAwal', 'tanggalAkhir', 'filterType', 'selectedYear'));
    }


    public function getDetailPengunjung(Request $request)
    {
        $tanggal = $request->input('tanggal'); // YYYY-MM-DD
        $bulanTahun = $request->input('bulan'); // YYYY-MM
        $kodeIdentifikasi = $request->input('kode_identifikasi');

        if ((!$tanggal && !$bulanTahun) || !$kodeIdentifikasi) {
            return response()->json(['error' => 'Parameter tidak lengkap.'], 400);
        }

        $query = M_vishistory::select(
            'visitorhistory.cardnumber',
            'borrowers.surname as nama',
            DB::raw('COUNT(visitorhistory.id) as visit_count')
        )
            ->leftJoin('borrowers', 'visitorhistory.cardnumber', '=', 'borrowers.cardnumber');

        // Tentukan rentang waktu berdasarkan filter yang ada
        if ($bulanTahun) {
            // Logika untuk filter per tahun (per bulan)
            $query->where(DB::raw('DATE_FORMAT(visitorhistory.visittime, "%Y-%m")'), $bulanTahun);
        } else {
            // Logika untuk filter per hari
            $startOfDay = Carbon::parse($tanggal)->startOfDay()->toDateTimeString();
            $endOfDay = Carbon::parse($tanggal)->endOfDay()->toDateTimeString();
            $query->whereBetween('visitorhistory.visittime', [$startOfDay, $endOfDay]);
        }

        if (strtoupper($kodeIdentifikasi) === 'DOSEN_TENDIK') {
            $query->whereRaw('LENGTH(visitorhistory.cardnumber) <= 6');
        } else {
            $query->whereRaw('SUBSTR(visitorhistory.cardnumber, 1, 4) = ?', [$kodeIdentifikasi]);
        }

        $detailPengunjung = $query
            ->groupBy('visitorhistory.cardnumber', 'borrowers.surname')
            ->orderBy('visit_count', 'desc') // Mengurutkan dari kunjungan terbanyak
            ->get();

        return response()->json($detailPengunjung);
    }

    public function getProdiExportData(Request $request)
    {
        // Ambil parameter dari request
        $filterType = $request->input('filter_type', 'daily');
        $kodeProdiFilter = $request->input('prodi');

        // Tentukan rentang tanggal berdasarkan filter
        if ($filterType === 'yearly') {
            $selectedYear = $request->input('tahun');
            if (!$selectedYear) {
                return response()->json(['error' => 'Tahun harus diisi.'], 400);
            }
            $tanggalAwal = Carbon::createFromDate($selectedYear, 1, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($selectedYear, 12, 31)->format('Y-m-d');
        } else { // 'daily'
            $tanggalAwal = $request->input('tanggal_awal');
            $tanggalAkhir = $request->input('tanggal_akhir');
            if (!$tanggalAwal || !$tanggalAkhir) {
                return response()->json(['error' => 'Tanggal awal dan akhir harus diisi.'], 400);
            }
        }

        $visitorHistoryTable = (new M_vishistory())->getTable();

        // Perbaikan di sini: Logika SELECT dan GROUP BY disesuaikan dengan filterType
        $query = M_vishistory::query();

        if ($filterType === 'yearly') {
            $query->selectRaw('
            DATE_FORMAT(visittime, "%Y-%m") as bulan,
            CASE
                WHEN LENGTH(' . $visitorHistoryTable . '.cardnumber) <= 6 THEN "DOSEN_TENDIK"
                ELSE SUBSTR(' . $visitorHistoryTable . '.cardnumber, 1, 4)
            END as kode_identifikasi,
            COUNT(id) as jumlah_kunjungan
        ')
                ->groupBy(
                    DB::raw('DATE_FORMAT(visittime, "%Y-%m")'),
                    DB::raw('CASE WHEN LENGTH(' . $visitorHistoryTable . '.cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(' . $visitorHistoryTable . '.cardnumber, 1, 4) END')
                )
                ->orderBy('bulan', 'asc');
        } else {
            $query->selectRaw('
            DATE(visittime) as tanggal_kunjungan,
            CASE
                WHEN LENGTH(' . $visitorHistoryTable . '.cardnumber) <= 6 THEN "DOSEN_TENDIK"
                ELSE SUBSTR(' . $visitorHistoryTable . '.cardnumber, 1, 4)
            END as kode_identifikasi,
            COUNT(id) as jumlah_kunjungan
        ')
                ->groupBy(
                    DB::raw('DATE(visittime)'),
                    DB::raw('CASE WHEN LENGTH(' . $visitorHistoryTable . '.cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(' . $visitorHistoryTable . '.cardnumber, 1, 4) END')
                )
                ->orderBy('tanggal_kunjungan', 'asc');
        }

        // Bagian whereBetween tetap sama
        $query->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);

        // Bagian filter prodi juga tetap sama
        if (!empty($kodeProdiFilter)) {
            if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
                $query->whereRaw('LENGTH(' . $visitorHistoryTable . '.cardnumber) <= 6');
            } else {
                $query->whereRaw('SUBSTR(' . $visitorHistoryTable . '.cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
            }
        }

        $data = $query->get();

        $prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray() + ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'];
        $data->transform(function ($item) use ($prodiMapping) {
            $item->nama_prodi = $prodiMapping[strtoupper($item->kode_identifikasi)] ?? 'Prodi Tidak Dikenal';
            return $item;
        });

        return response()->json(['data' => $data]);
    }

    public function kunjunganTanggalTable(Request $request)
    {
        $filterType = $request->input('filter_type', 'daily'); // Default 'daily'
        $tanggalAwal = null;
        $tanggalAkhir = null;
        $selectedYear = null;

        // Tambahkan baris ini untuk mendapatkan nilai per_page
        $perPage = $request->input('per_page', 10);
        if (!in_array($perPage, [10, 100, 1000])) {
            $perPage = 10;
        }

        // Base query yang akan digunakan untuk table dan chart
        $baseQuery = M_vishistory::query();

        if ($filterType === 'yearly') {
            $selectedYear = $request->input('tahun', Carbon::now()->year);

            // Validasi dan set tahun jika perlu
            if (!is_numeric($selectedYear)) {
                $selectedYear = Carbon::now()->year;
            }

            $tanggalAwal = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear()->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear()->format('Y-m-d');

            $baseQuery->select(
                DB::raw('DATE_FORMAT(visittime, "%Y-%m-01") as tanggal_kunjungan'),
                DB::raw('COUNT(id) as jumlah_kunjungan_harian')
            )
                ->groupBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'))
                ->orderBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'), 'asc');
        } else { // filterType === 'daily'
            $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));

            // Validasi tanggal
            if (Carbon::parse($tanggalAwal)->greaterThan(Carbon::parse($tanggalAkhir))) {
                return redirect()->back()->withInput($request->all())->with('error', 'Tanggal Awal tidak boleh lebih besar dari Tanggal Akhir.');
            }

            $baseQuery->selectRaw('
            DATE(visittime) as tanggal_kunjungan,
            COUNT(id) as jumlah_kunjungan_harian
        ')
                ->groupBy(DB::raw('DATE(visittime)'))
                ->orderBy(DB::raw('DATE(visittime)'), 'asc');
        }

        // Terapkan filter tanggal ke query
        $baseQuery->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);

        // Data untuk tabel (dipaginasi)
        // Ganti nilai `10` dengan variabel `$perPage`
        $data = (clone $baseQuery)->paginate($perPage);

        // Data untuk chart (TIDAK DIPAGINASI)
        $chartData = (clone $baseQuery)->get();
        $totalKeseluruhanKunjungan = $chartData->sum('jumlah_kunjungan_harian');

        // Pastikan semua parameter filter ditambahkan ke pagination link
        $data->appends($request->all());

        // Teruskan variabel $perPage ke view
        return view('pages.kunjungan.tanggalTable', compact('data', 'totalKeseluruhanKunjungan', 'filterType', 'tanggalAwal', 'tanggalAkhir', 'selectedYear', 'chartData', 'perPage'));
    }

    // public function kunjunganTanggalTable(Request $request)
    // {
    //     $filterType = $request->input('filter_type', 'daily'); // Default 'daily'
    //     $tanggalAwal = null;
    //     $tanggalAkhir = null;
    //     $selectedYear = null;

    //     // Base query yang akan digunakan untuk table dan chart
    //     $baseQuery = M_vishistory::query();

    //     if ($filterType === 'yearly') {
    //         $selectedYear = $request->input('tahun', Carbon::now()->year);

    //         // Validasi dan set tahun jika perlu
    //         if (!is_numeric($selectedYear)) {
    //             $selectedYear = Carbon::now()->year;
    //         }

    //         $tanggalAwal = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear()->format('Y-m-d');
    //         $tanggalAkhir = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear()->format('Y-m-d');

    //         $baseQuery->select(
    //             DB::raw('DATE_FORMAT(visittime, "%Y-%m-01") as tanggal_kunjungan'),
    //             DB::raw('COUNT(id) as jumlah_kunjungan_harian')
    //         )
    //             ->groupBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'))
    //             ->orderBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'), 'asc');
    //     } else { // filterType === 'daily'
    //         $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
    //         $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));

    //         // Validasi tanggal
    //         if (Carbon::parse($tanggalAwal)->greaterThan(Carbon::parse($tanggalAkhir))) {
    //             return redirect()->back()->withInput($request->all())->with('error', 'Tanggal Awal tidak boleh lebih besar dari Tanggal Akhir.');
    //         }

    //         $baseQuery->selectRaw('
    //             DATE(visittime) as tanggal_kunjungan,
    //             COUNT(id) as jumlah_kunjungan_harian
    //         ')
    //             ->groupBy(DB::raw('DATE(visittime)'))
    //             ->orderBy(DB::raw('DATE(visittime)'), 'asc');
    //     }

    //     // Terapkan filter tanggal ke query
    //     $baseQuery->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);

    //     // Data untuk tabel (dipaginasi)
    //     $data = (clone $baseQuery)->paginate(10);

    //     // Data untuk chart (TIDAK DIPAGINASI)
    //     $chartData = (clone $baseQuery)->get();
    //     $totalKeseluruhanKunjungan = $chartData->sum('jumlah_kunjungan_harian');
    //     $data->appends($request->all());

    //     return view('pages.kunjungan.tanggalTable', compact('data', 'totalKeseluruhanKunjungan', 'filterType', 'tanggalAwal', 'tanggalAkhir', 'selectedYear', 'chartData'));
    // }

    // public function getDetailPengunjungHarian(Request $request)
    // {
    //     $tanggal = $request->input('tanggal'); // Ini akan menjadi 'YYYY-MM-DD'
    //     $page = $request->input('page', 1);

    //     if (!$tanggal) {
    //         return response()->json(['error' => 'Tanggal tidak ditemukan.'], 400);
    //     }

    //     // Tentukan apakah tanggal yang dikirim adalah awal bulan (untuk deteksi filter tahunan)
    //     $dateCarbon = Carbon::parse($tanggal);
    //     $isFirstDayOfMonth = ($dateCarbon->day == 1);
    //     $month = $dateCarbon->month;
    //     $year = $dateCarbon->year;

    //     $query = M_vishistory::query();
    //     $totalVisitorsQuery = M_vishistory::query();

    //     // Jika ini adalah awal bulan dan kita berasumsi ini dari filter tahunan,
    //     // kita akan mencari data untuk seluruh bulan tersebut.
    //     // Jika tidak, kita cari per hari.
    //     if ($isFirstDayOfMonth) {
    //         $startDateOfMonth = Carbon::createFromDate($year, $month, 1)->startOfDay();
    //         $endDateOfMonth = Carbon::createFromDate($year, $month)->endOfMonth()->endOfDay();

    //         $query->whereBetween('visittime', [$startDateOfMonth, $endDateOfMonth]);
    //         $totalVisitorsQuery->whereBetween('visittime', [$startDateOfMonth, $endDateOfMonth]);
    //     } else {
    //         // Ini untuk kasus filter harian yang klik detail per hari
    //         $query->whereDate('visittime', $tanggal);
    //         $totalVisitorsQuery->whereDate('visittime', $tanggal);
    //     }

    //     // Get total visitors for the determined period
    //     $totalVisitors = $totalVisitorsQuery->count();

    //     // Get paginated unique visitors for the determined period
    //     // Pastikan nama tabel 'visitorhistory' dan 'borrowers' sudah benar.
    //     // Asumsi: M_vishistory mengacu ke 'visitorhistory'
    //     // Asumsi: Join menggunakan 'cardnumber' di kedua tabel. Jika 'borrowers' punya 'borrowernumber', sesuaikan.
    //     $visitors = $query->select(
    //         'visitorhistory.cardnumber',
    //         'borrowers.surname',
    //         DB::raw('COUNT(visitorhistory.id) as visit_count')
    //     )
    //         ->join('borrowers', 'visitorhistory.cardnumber', '=', 'borrowers.cardnumber') // Sesuaikan join key jika perlu
    //         ->groupBy('visitorhistory.cardnumber', 'borrowers.surname')
    //         ->orderBy('borrowers.surname', 'asc')
    //         ->paginate(5, ['*'], 'page', $page);

    //     $formattedVisitors = $visitors->map(function ($visitor) {
    //         return [
    //             'cardnumber' => $visitor->cardnumber,
    //             'nama' => $visitor->surname,
    //             'visit_count' => $visitor->visit_count,
    //         ];
    //     });

    //     // Tentukan tanggal yang akan ditampilkan di modal
    //     $displayTanggal = $tanggal;
    //     if ($isFirstDayOfMonth) {
    //         $displayTanggal = Carbon::createFromDate($year, $month, 1)->format('F Y'); // Contoh: "January 2024"
    //     } else {
    //         $displayTanggal = Carbon::parse($tanggal)->format('d F Y'); // Contoh: "01 January 2024"
    //     }


    //     return response()->json([
    //         'data' => $formattedVisitors,
    //         'total' => $totalVisitors,
    //         'current_page' => $visitors->currentPage(),
    //         'last_page' => $visitors->lastPage(),
    //         'per_page' => $visitors->perPage(),
    //         'from' => $visitors->firstItem(),
    //         'to' => $visitors->lastItem(),
    //         'modal_display_date' => $displayTanggal // Mengirim format tanggal untuk modal
    //     ]);
    // }

    public function getDetailPengunjungHarian(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $page = $request->input('page', 1);

        if (!$tanggal) {
            return response()->json(['error' => 'Tanggal tidak ditemukan.'], 400);
        }

        $dateCarbon = Carbon::parse($tanggal);
        $isFirstDayOfMonth = ($dateCarbon->day == 1);
        $startDate = null;
        $endDate = null;

        if ($isFirstDayOfMonth) {
            $startDate = $dateCarbon->startOfMonth()->toDateTimeString();
            $endDate = $dateCarbon->endOfMonth()->toDateTimeString();
            $displayTanggal = $dateCarbon->format('F Y');
        } else {
            $startDate = $dateCarbon->startOfDay()->toDateTimeString();
            $endDate = $dateCarbon->endOfDay()->toDateTimeString();
            $displayTanggal = $dateCarbon->format('d F Y');
        }

        // Query untuk menghitung total kunjungan (bukan pengunjung unik)
        $totalVisitors = M_vishistory::whereBetween('visittime', [$startDate, $endDate])->count();

        // Query untuk mendapatkan pengunjung unik
        $visitors = M_vishistory::select(
            'visitorhistory.cardnumber',
            'borrowers.surname',
            DB::raw('COUNT(visitorhistory.id) as visit_count')
        )
            ->join('borrowers', 'visitorhistory.cardnumber', '=', 'borrowers.cardnumber')
            ->whereBetween('visittime', [$startDate, $endDate])
            ->groupBy('visitorhistory.cardnumber', 'borrowers.surname')
            ->orderBy('borrowers.surname', 'asc')
            ->paginate(5, ['*'], 'page', $page);

        $formattedVisitors = $visitors->map(function ($visitor) {
            return [
                'cardnumber' => $visitor->cardnumber,
                'nama' => $visitor->surname,
                'visit_count' => $visitor->visit_count,
            ];
        });

        return response()->json([
            'data' => $formattedVisitors,
            'total' => $totalVisitors,
            'current_page' => $visitors->currentPage(),
            'last_page' => $visitors->lastPage(),
            'per_page' => $visitors->perPage(),
            'from' => $visitors->firstItem(),
            'to' => $visitors->lastItem(),
            'modal_display_date' => $displayTanggal
        ]);
    }


    public function getKunjunganHarianExportData(Request $request)
    {
        $filterType = $request->input('filter_type', 'daily');
        $tanggalAwal = null;
        $tanggalAkhir = null;
        $selectedYear = null;
        $exportData = collect(); // Inisialisasi koleksi kosong

        if ($filterType === 'yearly') {
            $selectedYear = $request->input('tahun');

            if (!$selectedYear || !is_numeric($selectedYear)) {
                $selectedYear = Carbon::now()->year;
            }

            $tanggalAwal = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear()->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear()->format('Y-m-d');

            // --- PERBAIKAN PADA QUERY INI ---
            $dataFromDb = M_vishistory::select(
                DB::raw('DATE_FORMAT(visittime, "%Y-%m-01") as tanggal_kunjungan'),
                DB::raw('COUNT(id) as jumlah_kunjungan_harian')
            )
                ->where('visittime', '>=', $tanggalAwal . ' 00:00:00')
                ->where('visittime', '<=', $tanggalAkhir . ' 23:59:59')
                ->groupBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'))
                ->orderBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'), 'asc')
                ->get();

            // --- KODE BARU UNTUK MEMASTIKAN KONSISTENSI FORMAT ---
            // Kita akan memformat ulang setiap item menggunakan Carbon sebelum dikirim
            $exportData = $dataFromDb->map(function ($item) {
                // Pastikan format tanggal selalu YYYY-MM-DD
                $item->tanggal_kunjungan = Carbon::parse($item->tanggal_kunjungan)->format('Y-m-d');
                return $item;
            });
        } else { // filterType === 'daily'
            $tanggalAwal = $request->input('tanggal_awal');
            $tanggalAkhir = $request->input('tanggal_akhir');

            if (!$tanggalAwal || !$tanggalAkhir) {
                return response()->json(['error' => 'Tanggal awal dan akhir wajib diisi untuk filter harian.'], 400);
            }

            $exportData = M_vishistory::selectRaw('
                DATE(visittime) as tanggal_kunjungan,
                COUNT(id) as jumlah_kunjungan_harian
            ')
                ->where('visittime', '>=', $tanggalAwal . ' 00:00:00')
                ->where('visittime', '<=', $tanggalAkhir . ' 23:59:59')
                ->groupBy(DB::raw('DATE(visittime)'))
                ->orderBy(DB::raw('DATE(visittime)'), 'asc')
                ->get();
        }

        if (Carbon::parse($tanggalAwal)->greaterThan(Carbon::parse($tanggalAkhir))) {
            return response()->json(['error' => 'Tanggal Awal tidak boleh lebih besar dari Tanggal Akhir.'], 400);
        }

        return response()->json(['data' => $exportData]);
    }

    // public function getKunjunganHarianExportData(Request $request)
    // {
    //     $filterType = $request->input('filter_type', 'daily');
    //     $tanggalAwal = null;
    //     $tanggalAkhir = null;
    //     $selectedYear = null;

    //     $baseQuery = M_vishistory::query();

    //     if ($filterType === 'yearly') {
    //         $selectedYear = $request->input('tahun', Carbon::now()->year);

    //         if (!is_numeric($selectedYear)) {
    //             return response()->json(['error' => 'Tahun tidak valid.'], 400);
    //         }

    //         $tanggalAwal = Carbon::createFromDate($selectedYear, 1, 1)->startOfYear()->format('Y-m-d');
    //         $tanggalAkhir = Carbon::createFromDate($selectedYear, 12, 31)->endOfYear()->format('Y-m-d');

    //         $baseQuery->select(
    //             DB::raw('DATE_FORMAT(visittime, "%Y-%m-01") as tanggal_kunjungan'),
    //             DB::raw('COUNT(id) as jumlah_kunjungan_harian')
    //         )
    //             ->groupBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'))
    //             ->orderBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m-01")'), 'asc');
    //     } else { // filterType === 'daily'
    //         $tanggalAwal = $request->input('tanggal_awal');
    //         $tanggalAkhir = $request->input('tanggal_akhir');

    //         if (!$tanggalAwal || !$tanggalAkhir || Carbon::parse($tanggalAwal)->greaterThan(Carbon::parse($tanggalAkhir))) {
    //             return response()->json(['error' => 'Input tanggal tidak valid.'], 400);
    //         }

    //         $baseQuery->selectRaw('
    //             DATE(visittime) as tanggal_kunjungan,
    //             COUNT(id) as jumlah_kunjungan_harian
    //         ')
    //             ->groupBy(DB::raw('DATE(visittime)'))
    //             ->orderBy(DB::raw('DATE(visittime)'), 'asc');
    //     }

    //     $exportData = $baseQuery
    //         ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59'])
    //         ->get();

    //     return response()->json(['data' => $exportData]);
    // }

    public function cekKehadiran(Request $request)
    {
        $cardnumber = $request->input('cardnumber');

        $dataKunjungan = collect();
        $borrowerInfo = null;
        $fullBorrowerDetails = null;
        $pesan = 'Silakan masukkan Nomor Kartu Anggota (Cardnumber) untuk melihat laporan kunjungan.';

        if ($cardnumber) {
            $borrowerInfo = M_vishistory::where('cardnumber', $cardnumber)->first();

            if ($borrowerInfo) {
                $fullBorrowerDetails = DB::connection('mysql2')->table('borrowers')
                    ->select('borrowernumber', 'cardnumber', 'firstname', 'surname', 'email', 'phone')
                    ->where('cardnumber', $cardnumber)
                    ->first();

                if ($fullBorrowerDetails) {
                    $dataKunjungan = M_vishistory::selectRaw('
                            EXTRACT(YEAR_MONTH FROM visittime) as tahun_bulan,
                            COUNT(id) as jumlah_kunjungan
                        ')
                        ->where('cardnumber', $cardnumber)
                        ->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'))
                        ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'), 'asc')
                        ->paginate(10)
                        ->withQueryString();

                    if ($dataKunjungan->isEmpty()) {
                        $pesan = 'Tidak ada data kunjungan ditemukan untuk Nomor Kartu Anggota: ' . $cardnumber . ' (' . $fullBorrowerDetails->firstname . ' ' . $fullBorrowerDetails->surname . ').';
                    } else {
                        $pesan = null;
                    }
                } else {
                    $pesan = 'Detail peminjam tidak ditemukan di database utama untuk Nomor Kartu Anggota: ' . $cardnumber . '.';
                }
            } else {
                $pesan = 'Nomor Kartu Anggota (Cardnumber) tidak ditemukan dalam histori kunjungan.';
            }
        }

        return view('pages.kunjungan.cekKehadiran', compact('dataKunjungan', 'borrowerInfo', 'fullBorrowerDetails', 'pesan', 'cardnumber'));
    }

    public function getKehadiranExportData(Request $request)
    {
        $cardnumber = $request->input('cardnumber');

        if (!$cardnumber) {
            return response()->json(['error' => 'Nomor Kartu Anggota (Cardnumber) diperlukan.'], 400);
        }
        $borrowerInfo = M_vishistory::where('cardnumber', $cardnumber)->first();

        if (!$borrowerInfo) {
            return response()->json(['error' => 'Nomor Kartu Anggota (Cardnumber) tidak ditemukan dalam histori kunjungan.'], 404);
        }

        $dataKunjungan = M_vishistory::on('mysql2')
            ->selectRaw('
                EXTRACT(YEAR_MONTH FROM visittime) as tahun_bulan,
                COUNT(id) as jumlah_kunjungan
            ')
            ->where('cardnumber', $cardnumber)
            ->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'))
            ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'), 'asc')
            ->get();

        $fullBorrowerDetails = DB::connection('mysql2')->table('borrowers')
            ->select('cardnumber', 'firstname', 'surname')
            ->where('cardnumber', $cardnumber)
            ->first();

        $exportData = $dataKunjungan->map(function ($row) {
            return [
                'bulan_tahun' => Carbon::createFromFormat('Ym', $row->tahun_bulan)->format('M Y'),
                'jumlah_kunjungan' => $row->jumlah_kunjungan,
            ];
        });

        return response()->json([
            'data' => $exportData,
            'borrower_name' => $fullBorrowerDetails ? $fullBorrowerDetails->firstname . ' ' . $fullBorrowerDetails->surname : 'Unknown',
            'cardnumber' => $cardnumber,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $cardnumber = $request->input('cardnumber');

        // Validasi atau cek apakah cardnumber ada
        if (!$cardnumber) {
            return redirect()->back()->with('error', 'Nomor Kartu Anggota harus diisi untuk mengekspor laporan.');
        }

        // Ambil data dari database (sama seperti di method cekKehadiran)
        $fullBorrowerDetails = DB::connection('mysql2')->table('borrowers')
            ->select('cardnumber', 'firstname', 'surname', 'email', 'phone')
            ->where('cardnumber', $cardnumber)
            ->first();

        $dataKunjungan = M_vishistory::on('mysql2')
            ->selectRaw('
            EXTRACT(YEAR_MONTH FROM visittime) as tahun_bulan,
            COUNT(id) as jumlah_kunjungan
        ')
            ->where('cardnumber', $cardnumber)
            ->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'))
            ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'), 'asc')
            ->get();

        // Cek jika data tidak ditemukan
        if (!$fullBorrowerDetails || $dataKunjungan->isEmpty()) {
            return redirect()->back()->with('error', 'Data tidak ditemukan untuk Nomor Kartu Anggota tersebut.');
        }

        // Kirim data ke view Blade khusus PDF
        $pdf = PDF::loadView('pages.kunjungan.laporan_kehadiran_pdf', compact('fullBorrowerDetails', 'dataKunjungan'));

        // Unduh PDF
        return $pdf->download('laporan_kehadiran_' . $cardnumber . '.pdf');
    }
}
