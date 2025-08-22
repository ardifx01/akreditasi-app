<?php

namespace App\Http\Controllers;

use App\Models\M_eprodi;
use App\Models\M_vishistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;

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

    // public function kunjunganProdiTable(Request $request)
    // {
    //     $listProdi = M_eprodi::pluck('nama', 'kode')->toArray();
    //     $listProdi = ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'] + $listProdi;

    //     $filterType = $request->input('filter_type', 'daily');
    //     $kodeProdiFilter = $request->input('prodi');
    //     $perPage = $request->input('per_page', 10);

    //     // Cek apakah ada parameter filter yang disubmit
    //     $hasFilter = $request->has('filter_type') || $request->has('prodi') || $request->has('tanggal_awal') || $request->has('tahun');

    //     // Inisialisasi variabel dengan nilai default (kosong)
    //     $data = collect([]);
    //     $chartData = collect([]);
    //     $totalKeseluruhanKunjungan = 0;
    //     $tanggalAwal = null;
    //     $tanggalAkhir = null;
    //     $tahun = null;
    //     $displayPeriod = '';

    //     if ($hasFilter) {
    //         // Logika pengambilan data hanya dieksekusi jika ada filter
    //         if ($filterType === 'yearly') {
    //             $tahun = $request->input('tahun', Carbon::now()->year);
    //             $tanggalAwal = Carbon::createFromDate($tahun, 1, 1)->format('Y-m-d');
    //             $tanggalAkhir = Carbon::createFromDate($tahun, 12, 31)->format('Y-m-d');
    //             $displayPeriod = "Tahun " . $tahun;
    //         } else { // 'daily'
    //             $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->toDateString());
    //             $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->toDateString());
    //             $displayPeriod = "Periode " . Carbon::parse($tanggalAwal)->locale('id')->isoFormat('D MMMM Y') . " s.d. " . Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y');
    //         }

    //         // Kueri untuk total keseluruhan
    //         $totalKeseluruhanQuery = M_vishistory::query()
    //             ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
    //         if (!empty($kodeProdiFilter)) {
    //             if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
    //                 $totalKeseluruhanQuery->whereRaw('LENGTH(cardnumber) <= 6');
    //             } else {
    //                 $totalKeseluruhanQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
    //             }
    //         }
    //         $totalKeseluruhanKunjungan = $totalKeseluruhanQuery->count();

    //         // Kueri utama untuk tabel
    //         $baseQuery = M_vishistory::selectRaw('
    //             ' . ($filterType === 'yearly' ? 'DATE_FORMAT(visittime, "%Y-%m")' : 'DATE(visittime)') . ' as tanggal_kunjungan,
    //             CASE
    //                 WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK"
    //                 ELSE SUBSTR(cardnumber, 1, 4)
    //             END as kode_identifikasi,
    //             COUNT(id) as jumlah_kunjungan_harian
    //         ')
    //             ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
    //         if (!empty($kodeProdiFilter)) {
    //             if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
    //                 $baseQuery->whereRaw('LENGTH(cardnumber) <= 6');
    //             } else {
    //                 $baseQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
    //             }
    //         }
    //         $baseQuery->groupBy('tanggal_kunjungan', 'kode_identifikasi')
    //             ->orderBy('tanggal_kunjungan', 'asc')
    //             ->orderBy('kode_identifikasi', 'asc');
    //         $data = $baseQuery->paginate($perPage);

    //         $prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray() + ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'];
    //         $data->getCollection()->transform(function ($item) use ($prodiMapping) {
    //             $item->nama_prodi = $prodiMapping[strtoupper($item->kode_identifikasi)] ?? 'Prodi Tidak Dikenal';
    //             $item->kode_prodi = $item->kode_identifikasi;
    //             return $item;
    //         });
    //         $data->appends($request->all());

    //         // Kueri untuk data chart
    //         $chartDataQuery = M_vishistory::selectRaw('
    //             ' . ($filterType === 'yearly' ? 'DATE_FORMAT(visittime, "%Y-%m")' : 'DATE(visittime)') . ' as label,
    //             COUNT(id) as total_kunjungan
    //         ')
    //             ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
    //         if (!empty($kodeProdiFilter)) {
    //             if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
    //                 $chartDataQuery->whereRaw('LENGTH(cardnumber) <= 6');
    //             } else {
    //                 $chartDataQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
    //             }
    //         }
    //         if ($filterType === 'yearly') {
    //             $chartDataQuery->groupBy(DB::raw('DATE_FORMAT(visittime, "%Y-%m")'))
    //                 ->orderBy('label', 'asc');
    //         } else {
    //             $chartDataQuery->groupBy(DB::raw('DATE(visittime)'))
    //                 ->orderBy('label', 'asc');
    //         }
    //         $chartData = $chartDataQuery->get();
    //     }

    //     // Mengirimkan variabel $hasFilter ke view
    //     return view('pages.kunjungan.prodiTable', compact('data', 'listProdi', 'tanggalAwal', 'tanggalAkhir', 'filterType', 'tahun', 'perPage', 'displayPeriod', 'chartData', 'totalKeseluruhanKunjungan', 'hasFilter'));
    // }

    public function kunjunganProdiTable(Request $request)
    {
        $listProdi = M_eprodi::pluck('nama', 'kode')->toArray();
        $listProdi = ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'] + $listProdi;

        $filterType = $request->input('filter_type', 'daily');
        $kodeProdiFilter = $request->input('prodi');
        $perPage = $request->input('per_page', 10);

        // Cek apakah ada parameter filter yang disubmit
        $hasFilter = $request->has('filter_type') || $request->has('prodi') || $request->has('tanggal_awal') || $request->has('tahun_awal');

        // Inisialisasi variabel dengan nilai default (kosong)
        $data = collect([]);
        $chartData = collect([]);
        $totalKeseluruhanKunjungan = 0;
        $tanggalAwal = null;
        $tanggalAkhir = null;
        $tahunAwal = null;
        $tahunAkhir = null;
        $displayPeriod = '';

        if ($hasFilter) {
            // Logika pengambilan data hanya dieksekusi jika ada filter
            if ($filterType === 'yearly') {
                $tahunAwal = $request->input('tahun_awal', Carbon::now()->year);
                $tahunAkhir = $request->input('tahun_akhir', Carbon::now()->year);
                // Pastikan tahun awal tidak lebih besar dari tahun akhir
                if ($tahunAwal > $tahunAkhir) {
                    $tahunAwal = $tahunAkhir;
                }
                $tanggalAwal = Carbon::createFromDate($tahunAwal, 1, 1)->format('Y-m-d');
                $tanggalAkhir = Carbon::createFromDate($tahunAkhir, 12, 31)->format('Y-m-d');
                $displayPeriod = "Tahun " . $tahunAwal . " s.d. " . $tahunAkhir;
            } else { // 'daily'
                $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->toDateString());
                $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->toDateString());
                $displayPeriod = "Periode " . Carbon::parse($tanggalAwal)->locale('id')->isoFormat('D MMMM Y') . " s.d. " . Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y');
            }

            // Kueri untuk total keseluruhan
            $totalKeseluruhanQuery = M_vishistory::query()
                ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
            if (!empty($kodeProdiFilter) && strtolower($kodeProdiFilter) !== 'semua') {
                if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
                    $totalKeseluruhanQuery->whereRaw('LENGTH(cardnumber) <= 6');
                } else {
                    $totalKeseluruhanQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
                }
            }
            $totalKeseluruhanKunjungan = $totalKeseluruhanQuery->count();

            // Kueri utama untuk tabel
            $baseQuery = M_vishistory::selectRaw('
            ' . ($filterType === 'yearly' ? 'DATE_FORMAT(visittime, "%Y-%m")' : 'DATE(visittime)') . ' as tanggal_kunjungan,
            CASE
                WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK"
                ELSE SUBSTR(cardnumber, 1, 4)
            END as kode_identifikasi,
            COUNT(id) as jumlah_kunjungan_harian
        ')
                ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
            if (!empty($kodeProdiFilter) && strtolower($kodeProdiFilter) !== 'semua') {
                if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
                    $baseQuery->whereRaw('LENGTH(cardnumber) <= 6');
                } else {
                    $baseQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
                }
            }
            $baseQuery->groupBy('tanggal_kunjungan', 'kode_identifikasi')
                ->orderBy('tanggal_kunjungan', 'asc')
                ->orderBy('kode_identifikasi', 'asc');
            $data = $baseQuery->paginate($perPage);

            $prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray() + ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'];
            $data->getCollection()->transform(function ($item) use ($prodiMapping) {
                $item->nama_prodi = $prodiMapping[strtoupper($item->kode_identifikasi)] ?? 'Prodi Tidak Dikenal';
                $item->kode_prodi = $item->kode_identifikasi;
                return $item;
            });
            $data->appends($request->all());

            // Kueri untuk data chart
            $chartDataQuery = M_vishistory::selectRaw('
            ' . ($filterType === 'yearly' ? 'DATE_FORMAT(visittime, "%Y-%m")' : 'DATE(visittime)') . ' as label,
            COUNT(id) as total_kunjungan
        ')
                ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);
            if (!empty($kodeProdiFilter) && strtolower($kodeProdiFilter) !== 'semua') {
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
        }

        // Mengirimkan variabel $hasFilter ke view
        return view('pages.kunjungan.prodiTable', compact('data', 'listProdi', 'tanggalAwal', 'tanggalAkhir', 'filterType', 'tahunAwal', 'tahunAkhir', 'perPage', 'displayPeriod', 'chartData', 'totalKeseluruhanKunjungan', 'hasFilter'));
    }

    public function getDetailPengunjung(Request $request)
    {
        $tanggal = $request->query('tanggal'); // YYYY-MM-DD
        $bulanTahun = $request->query('bulan'); // YYYY-MM
        $kodeIdentifikasi = $request->query('kode_identifikasi');
        $isExport = $request->query('export'); // Parameter baru untuk ekspor

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
            $query->where(DB::raw('DATE_FORMAT(visitorhistory.visittime, "%Y-%m")'), $bulanTahun);
        } else {
            $startOfDay = Carbon::parse($tanggal)->startOfDay()->toDateTimeString();
            $endOfDay = Carbon::parse($tanggal)->endOfDay()->toDateTimeString();
            $query->whereBetween('visitorhistory.visittime', [$startOfDay, $endOfDay]);
        }

        if (strtoupper($kodeIdentifikasi) === 'DOSEN_TENDIK') {
            $query->whereRaw('LENGTH(visitorhistory.cardnumber) <= 6');
        } else {
            $query->whereRaw('SUBSTR(visitorhistory.cardnumber, 1, 4) = ?', [$kodeIdentifikasi]);
        }

        $query->groupBy('visitorhistory.cardnumber', 'borrowers.surname')
            ->orderBy('visit_count', 'desc');

        if ($isExport) {
            // Jika permintaan adalah untuk ekspor, ambil semua data
            $detailPengunjung = $query->get();
        } else {
            // Jika bukan, gunakan paginasi seperti biasa
            $perPage = $request->input('per_page', 10);
            $detailPengunjung = $query->paginate($perPage);
        }

        return response()->json($detailPengunjung);
    }


    public function getProdiExportData(Request $request)
    {
        // Tentukan rentang tanggal
        $filterType = $request->input('filter_type', 'daily');
        if ($filterType === 'yearly') {
            $tahunAwal = $request->input('tahun_awal', Carbon::now()->year);
            $tahunAkhir = $request->input('tahun_akhir', Carbon::now()->year);
            if ($tahunAwal > $tahunAkhir) {
                $tahunAwal = $tahunAkhir;
            }
            $tanggalAwal = Carbon::createFromDate($tahunAwal, 1, 1)->format('Y-m-d');
            $tanggalAkhir = Carbon::createFromDate($tahunAkhir, 12, 31)->format('Y-m-d');
            $periodeDisplay = "Tahun " . $tahunAwal;
            if ($tahunAwal !== $tahunAkhir) {
                $periodeDisplay .= " s/d " . $tahunAkhir;
            }
        } else { // 'daily'
            $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->toDateString());
            $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->toDateString());
            $periodeDisplay = "Periode " . Carbon::parse($tanggalAwal)->locale('id')->isoFormat('D MMMM Y') . " s.d. " . Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y');
        }

        $kodeProdiFilter = $request->input('prodi');

        // Bangun kueri tanpa paginasi
        $baseQuery = M_vishistory::selectRaw('
        ' . ($filterType === 'yearly' ? 'DATE_FORMAT(visittime, "%Y-%m")' : 'DATE(visittime)') . ' as tanggal_kunjungan,
        CASE
            WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK"
            ELSE SUBSTR(cardnumber, 1, 4)
        END as kode_identifikasi,
        COUNT(id) as jumlah_kunjungan_harian
    ')
            ->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);

        // Tambahkan kondisi filter prodi jika tidak 'semua'
        if ($kodeProdiFilter && strtolower($kodeProdiFilter) !== 'semua') {
            if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
                $baseQuery->whereRaw('LENGTH(cardnumber) <= 6');
            } else {
                $baseQuery->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
            }
        }

        $data = $baseQuery->groupBy('tanggal_kunjungan', 'kode_identifikasi')
            ->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('kode_identifikasi', 'asc')
            ->get();

        // Map data dengan nama prodi
        $prodiMapping = M_eprodi::pluck('nama', 'kode')->toArray() + ['DOSEN_TENDIK' => 'Dosen / Tenaga Kependidikan'];
        $namaProdiFilter = $prodiMapping[strtoupper($kodeProdiFilter)] ?? 'Seluruh Prodi';
        $data->transform(function ($item) use ($prodiMapping) {
            $item->nama_prodi = $prodiMapping[strtoupper($item->kode_identifikasi)] ?? 'Prodi Tidak Dikenal';
            $item->kode_prodi = $item->kode_identifikasi;
            return $item;
        });

        // Buat file CSV
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kunjungan_prodi.csv"',
        ];

        $callback = function () use ($data, $filterType, $namaProdiFilter, $periodeDisplay) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Tambahkan baris judul seperti contoh
            fputcsv($file, ["Statistik Kunjungan per Program Studi: " . $namaProdiFilter], ';');
            // fputcsv($file, ["Periode;Jumlah Kunjungan"], ';');

            // Tambahkan baris kosong
            fputcsv($file, [''], ';');

            $headers = ['Tanggal / Bulan', 'Kode Prodi', 'Nama Prodi', 'Jumlah Kunjungan'];
            fputcsv($file, $headers, ';');

            foreach ($data as $row) {
                $tanggal = ($filterType === 'yearly') ?
                    \Carbon\Carbon::parse($row->tanggal_kunjungan)->locale('id')->isoFormat('MMMM Y') :
                    \Carbon\Carbon::parse($row->tanggal_kunjungan)->locale('id')->isoFormat('dddd, D MMMM Y');
                fputcsv($file, [
                    $tanggal,
                    $row->kode_prodi,
                    $row->nama_prodi,
                    $row->jumlah_kunjungan_harian
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function kunjunganTanggalTable(Request $request)
    {
        // Variabel untuk menyimpan status apakah filter sudah disubmit
        $hasFilter = $request->has('filter_type') || $request->has('tanggal_awal') || $request->has('tanggal_akhir') || $request->has('tahun');
        $filterType = $request->input('filter_type', 'daily');
        $tanggalAwal = null;
        $tanggalAkhir = null;
        $selectedYear = null;
        $perPage = $request->input('per_page', 10);
        $data = collect();
        $chartData = collect();
        $totalKeseluruhanKunjungan = 0;

        if (!in_array($perPage, [10, 100, 1000])) {
            $perPage = 10;
        }

        if ($hasFilter) {
            // Logika pengambilan data hanya dijalankan jika filter sudah disubmit
            $baseQuery = M_vishistory::query();

            if ($filterType === 'yearly') {
                $selectedYear = $request->input('tahun', Carbon::now()->year);
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

            $baseQuery->whereBetween('visittime', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);

            $data = (clone $baseQuery)->paginate($perPage);
            $chartData = (clone $baseQuery)->get();
            $totalKeseluruhanKunjungan = $chartData->sum('jumlah_kunjungan_harian');
            $data->appends($request->all());
        }

        // Teruskan variabel hasFilter ke view
        return view('pages.kunjungan.tanggalTable', compact('data', 'totalKeseluruhanKunjungan', 'filterType', 'tanggalAwal', 'tanggalAkhir', 'selectedYear', 'chartData', 'perPage', 'hasFilter'));
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
        $tahun = $request->input('tahun'); // Ambil input tahun

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
                    $query = M_vishistory::selectRaw('
                    EXTRACT(YEAR_MONTH FROM visittime) as tahun_bulan,
                    COUNT(id) as jumlah_kunjungan
                ')
                        ->where('cardnumber', $cardnumber);

                    // Tambahkan kondisi filter tahun
                    if ($tahun) {
                        $query->whereYear('visittime', $tahun);
                    }

                    $dataKunjungan = $query->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'))
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
        $tahun = $request->input('tahun');

        // Cek jika data tidak ditemukan setelah filter
        if (!$fullBorrowerDetails || $dataKunjungan->isEmpty()) {
            return redirect()->back()->with('error', 'Data tidak ditemukan untuk Nomor Kartu Anggota atau filter tahun yang dipilih.');
        }

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        // Kirim data dan opsi ke view Blade khusus PDF
        $pdf = PDF::loadView('pages.kunjungan.laporan_kehadiran_pdf', compact('fullBorrowerDetails', 'dataKunjungan'))
            ->setOptions($options);

        // Unduh PDF
        $fileName = 'laporan_kehadiran_' . $fullBorrowerDetails->cardnumber . '.pdf';
        return $pdf->download($fileName);
    }
}
