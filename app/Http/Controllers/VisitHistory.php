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
        'J520'    => 'Pendidikan Dokter Gigi',
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


    public function kunjunganProdiTable(Request $request)
    {
        $listProdi = M_eprodi::pluck('nama', 'kode')->toArray();
        $listProdi = ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'] + $listProdi;

        $filterType = $request->input('filter_type', 'daily');
        $kodeProdiFilter = $request->input('prodi');
        // Tambahkan ini untuk mengambil parameter per_page dari request
        $perPage = $request->input('per_page', 10);

        if ($filterType === 'yearly') {
            $selectedYear = $request->input('tahun', Carbon::now()->year);
            $tanggalAwal = Carbon::createFromDate($selectedYear, 1, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($selectedYear, 12, 31)->format('Y-m-d');
        } else { // 'daily'
            $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
            $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));
            $selectedYear = null;
        }

        // Query utama untuk tabel dan total kunjungan
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

        // Ambil data untuk total keseluruhan
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

        // Query terpisah untuk data chart (mengatasi error "Unknown column")
        $chartDataQuery = M_vishistory::selectRaw('
            ' . ($filterType === 'yearly' ? 'DATE_FORMAT(visittime, "%Y-%m")' : 'DATE(visittime)') . ' as label,
            COUNT(id) as total_kunjungan
        ')
            ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);

        if (!empty($kodeProdiFilter)) {
            if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
                $chartDataQuery->whereRaw('LENGTH(cardnumber) <= 6');
            } else {
                $chartDataQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
            }
        }

        if ($filterType === 'yearly') {
            $chartDataQuery->groupBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m")'))
                ->orderBy('label', 'asc');
        } else {
            $chartDataQuery->groupBy(DB::raw('DATE(visittime)'))
                ->orderBy('label', 'asc');
        }
        $chartData = $chartDataQuery->get();

        // Paginasi data utama untuk tabel
        if ($filterType === 'yearly') {
            $data = $baseQuery->groupBy(
                DB::raw('DATE_FORMAT(visittime, "%Y-%m")'),
                DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END')
            )
                ->orderBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m")'), 'asc')
                ->orderBy(DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END'), 'asc')
                ->paginate($perPage); // Gunakan variabel $perPage di sini
        } else {
            $data = $baseQuery->groupBy(
                DB::raw('DATE(visittime)'),
                DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END')
            )
                ->orderBy(DB::raw('DATE(visittime)'), 'asc')
                ->orderBy(DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END'), 'asc')
                ->paginate($perPage); // Gunakan variabel $perPage di sini
        }

        $prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray() + ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'];
        $data->getCollection()->transform(function ($item) use ($prodiMapping) {
            $item->nama_prodi = $prodiMapping[strtoupper($item->kode_identifikasi)] ?? 'Prodi Tidak Dikenal';
            $item->kode_prodi = $item->kode_identifikasi;
            return $item;
        });

        $data->appends($request->all());

        return view('pages.kunjungan.prodiTable', compact('data', 'listProdi', 'totalKeseluruhanKunjungan', 'tanggalAwal', 'tanggalAkhir', 'filterType', 'selectedYear', 'chartData', 'perPage'));
    }

    public function getDetailPengunjung(Request $request)
    {
        $tanggal = $request->query('tanggal'); // YYYY-MM-DD
        $bulanTahun = $request->query('bulan'); // YYYY-MM
        $kodeIdentifikasi = $request->query('kode_identifikasi');

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


        $data = (clone $baseQuery)->paginate($perPage);

        $chartData = (clone $baseQuery)->get();
        $totalKeseluruhanKunjungan = $chartData->sum('jumlah_kunjungan_harian');

        // Pastikan semua parameter filter ditambahkan ke pagination link
        $data->appends($request->all());

        // Teruskan variabel $perPage ke view
        return view('pages.kunjungan.tanggalTable', compact('data', 'totalKeseluruhanKunjungan', 'filterType', 'tanggalAwal', 'tanggalAkhir', 'selectedYear', 'chartData', 'perPage'));
    }



    public function getDetailPengunjungHarian(Request $request)
    {
        $tanggalKunjungan = $request->input('tanggal');
        $bulanTahun = $request->input('bulan'); // Ambil parameter 'bulan' dari request
        $kodeIdentifikasi = $request->input('kode_identifikasi');
        $page = $request->input('page', 1);

        // Tentukan periode berdasarkan parameter yang ada
        if ($bulanTahun) {
            $dateCarbon = \Carbon\Carbon::createFromFormat('Y-m', $bulanTahun);
            $startDate = $dateCarbon->startOfMonth()->toDateTimeString();
            $endDate = $dateCarbon->endOfMonth()->toDateTimeString();
            $displayTanggal = $dateCarbon->translatedFormat('F Y');
        } elseif ($tanggalKunjungan) {
            $dateCarbon = \Carbon\Carbon::parse($tanggalKunjungan);
            $startDate = $dateCarbon->startOfDay()->toDateTimeString();
            $endDate = $dateCarbon->endOfDay()->toDateTimeString();
            $displayTanggal = $dateCarbon->translatedFormat('d F Y');
        } else {
            return response()->json(['error' => 'Parameter tanggal/bulan tidak ditemukan.'], 400);
        }

        // Menggunakan Eloquent untuk Query
        $totalVisitors = M_vishistory::whereBetween('visittime', [$startDate, $endDate])->count();

        // Pastikan ada relationship 'borrower' di model M_vishistory
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
                // Ambil langsung dari hasil query, bukan relasi
                'nama' => $visitor->surname ?? 'Nama tidak ditemukan',
                'visit_count' => $visitor->visit_count,
            ];
        });

        return response()->json([
            'data' => $formattedVisitors,
            'total' => number_format($totalVisitors, 0, ',', '.'),
            'modal_display_date' => $displayTanggal,
            'current_page' => $visitors->currentPage(),
            'last_page' => $visitors->lastPage(),
            'from' => $visitors->firstItem(),
            'per_page' => $visitors->perPage(),
        ]);
    }

    public function getDetailPengunjungHarianExport(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $filterType = $request->input('filter_type', 'daily');

        if (!$tanggal) {
            return response()->json(['error' => 'Tanggal tidak ditemukan.'], 400);
        }

        $dateCarbon = \Carbon\Carbon::parse($tanggal);

        if ($filterType === 'yearly') {
            $startDate = $dateCarbon->startOfMonth()->toDateTimeString();
            $endDate = $dateCarbon->endOfMonth()->toDateTimeString();
        } else { // 'daily'
            $startDate = $dateCarbon->startOfDay()->toDateTimeString();
            $endDate = $dateCarbon->endOfDay()->toDateTimeString();
        }

        // Ambil SEMUA data tanpa pagination
        $visitors = M_vishistory::select(
            'visitorhistory.cardnumber',
            'borrowers.surname',
            DB::raw('COUNT(visitorhistory.id) as visit_count')
        )
            ->join('borrowers', 'visitorhistory.cardnumber', '=', 'borrowers.cardnumber')
            ->whereBetween('visittime', [$startDate, $endDate])
            ->groupBy('visitorhistory.cardnumber', 'borrowers.surname')
            ->orderBy('borrowers.surname', 'asc')
            ->get(); // Gunakan get() bukan paginate()

        $formattedVisitors = $visitors->map(function ($visitor) {
            return [
                'nama' => $visitor->surname ?? 'Nama tidak ditemukan',
                'cardnumber' => $visitor->cardnumber,
                'visit_count' => $visitor->visit_count,
            ];
        });

        return response()->json(['data' => $formattedVisitors]);
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
