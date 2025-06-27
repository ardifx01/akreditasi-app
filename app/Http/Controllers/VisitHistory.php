<?php

namespace App\Http\Controllers;

use App\Models\M_eprodi;
use App\Models\M_vishistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VisitHistory extends Controller
{
    public function kunjunganProdiChart(Request $request)
    {
        $listProdi = M_eprodi::pluck('nama', 'kode')->toArray();
        $prodi = $request->input('prodi');
        $thnDari = $request->input('tahun_awal');
        $thnSampai = $request->input('tahun_akhir');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';

        if ($prodi && $thnDari && $thnSampai) {
            $kodeProdiUntukFilter = ($prodi === 'all') ? array_keys($listProdi) : [$prodi];

            if ($prodi === 'all') {
                $namaProdi = 'Semua Prodi';
            } else {
                $namaProdi = $listProdi[$prodi] ?? 'Tidak Ditemukan';
            }

            $query = M_vishistory::selectRaw('
                EXTRACT(YEAR_MONTH FROM visitorhistory.visittime) as tahun_bulan,
                av.authorised_value as kode_prodi,
                le.nama as nama_prodi,
                COUNT(visitorhistory.id) as jumlah_kunjungan
            ')
                ->leftJoin('borrowers as b', 'visitorhistory.cardnumber', '=', 'b.cardnumber')
                ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
                ->leftJoin('authorised_values as av', function ($join) {
                    $join->on('ba.code', '=', 'av.category')
                        ->on('ba.attribute', '=', 'av.authorised_value');
                })
                ->leftJoin('local_eprodi as le', 'le.kode', '=', 'av.authorised_value')
                ->whereBetween(DB::raw('YEAR(visitorhistory.visittime)'), [(int)$thnDari, (int)$thnSampai]);

            if ($prodi !== 'all') {
                $query->whereIn('av.authorised_value', $kodeProdiUntukFilter);
            }

            $data = $query->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'av.authorised_value', 'le.nama')
                ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'asc')
                ->orderBy('av.authorised_value', 'asc')
                ->orderBy('le.nama', 'asc')
                ->paginate(12)
                ->withQueryString();
        }

        $selectedProdi = $request->input('prodi', '');
        $selectedTahunAwal = $request->input('tahun_awal', now()->year - 2);
        $selectedTahunAkhir = $request->input('tahun_akhir', now()->year);

        return view('pages.kunjungan.prodiChart', compact('data', 'listProdi', 'namaProdi', 'selectedProdi', 'selectedTahunAwal', 'selectedTahunAkhir'));
    }

    //  Data Mapping prodi
    private $prodiMapping = [
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
        'B10A'    => 'Management',
        'B200'    => 'Akuntansi',
        'B300'    => 'Ekonomi Pembangunan',
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
        'G000'    => 'Pendidikan Agama Islam',
        'G100'    => 'Ilmu Alquran dan Tafsir',
        'H100'    => 'Pondok Sobron',
        'I000'    => 'Hukum Ekonomi Syariah',
        'J100'    => 'Fisioterapi (D3)',
        'J120'    => 'Fisioterapi S1',
        'J130'    => 'Profesi Fisioterapi',
        'J210'    => 'Ilmu Keperawatan (S1)',
        'J230'    => 'Keperawatan Profesi (NERS)',
        'J310'    => 'Ilmu Gizi (S1)',
        'J410'    => 'Kesehatan Masyarakat (S1)',
        'J500'    => 'Pendidikan Dokter',
        'J510'    => 'Profesi Dokter',
        'J520	' => 'Pendidikan Dokter Gigi',
        'J530'    => 'Profesi Dokter Gigi',
        'K100'    => 'Farmasi',
        'K110'    => 'Profesi Apoteker',
        'L100'    => 'Ilmu Komunikasi',
        'L200'    => 'Informatika (Informatics)',
        'O000'    => 'Magister Studi Islam',
        'O100'    => 'Magister Pendidikan Islam',
        'O200'    => 'Magister Hukum Islam',
        'O300'    => 'Pendidikan Islam',
        'P100'    => 'Magister Manajemen',
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
        'W100'    => 'Magister Akuntansi',
    ];

    public function kunjunganProdiTable(Request $request)
    {
        $listProdi = M_eprodi::pluck('nama', 'kode')->toArray();

        $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $kodeProdiFilter = $request->input('prodi');

        $query = M_vishistory::selectRaw('
                DATE(visittime) as tanggal_kunjungan,
                CASE
                    WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK"
                    ELSE SUBSTR(cardnumber, 1, 4)
                END as kode_identifikasi,
                COUNT(id) as jumlah_kunjungan
            ')
            ->where('visittime', '>=', $tanggalAwal . ' 00:00:00')
            ->where('visittime', '<=', $tanggalAkhir . ' 23:59:59');


        if (!empty($kodeProdiFilter)) {
            if (strtoupper($kodeProdiFilter) === 'DOSEN_TENDIK') {
                $query->whereRaw('LENGTH(cardnumber) <= 6');
            } else {
                $query->whereRaw('SUBSTR(cardnumber, 1, 4) = ?', [$kodeProdiFilter]);
            }
        }

        $data = $query->groupBy(
            DB::raw('DATE(visittime)'),
            DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END')
        )
            // Mengurutkan hasil
            ->orderBy(DB::raw('DATE(visittime)'), 'asc')
            ->orderBy(DB::raw('CASE WHEN LENGTH(cardnumber) <= 6 THEN "DOSEN_TENDIK" ELSE SUBSTR(cardnumber, 1, 4) END'), 'asc')
            ->paginate(10);


        $data->getCollection()->transform(function ($item) {
            $item->nama_prodi = '';
            $item->kode_prodi = $item->kode_identifikasi;

            if (strtoupper($item->kode_identifikasi) === 'DOSEN_TENDIK') {
                $item->nama_prodi = 'Dosen / Tenaga Kependidikan';
            } else {
                $item->nama_prodi = $this->prodiMapping[strtoupper($item->kode_identifikasi)] ?? 'Prodi Tidak Dikenal';
            }
            return $item;
        });

        $data->appends($request->all());

        return view('pages.kunjungan.prodiTable', compact('data', 'listProdi'));
    }



    public function kunjunganTanggalTable(Request $request)
    {
        $tanggalAwal = $request->input('tanggal_awal', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->endOfMonth()->format('Y-m-d'));


        $query = M_vishistory::selectRaw('
                DATE(visittime) as tanggal_kunjungan,
                COUNT(id) as jumlah_kunjungan_harian
            ')
            ->where('visittime', '>=', $tanggalAwal . ' 00:00:00')
            ->where('visittime', '<=', $tanggalAkhir . ' 23:59:59');


        $data = $query->groupBy(DB::raw('DATE(visittime)'))
            ->orderBy(DB::raw('DATE(visittime)'), 'asc')
            ->paginate(10);


        $data->appends($request->only(['tanggal_awal', 'tanggal_akhir']));

        return view('pages.kunjungan.tanggalTable', compact('data'));
    }

    public function cekKehadiran(Request $request)
    {
        $cardnumber = $request->input('cardnumber');

        $dataKunjungan = collect();
        $borrowerInfo = null;
        $pesan = 'Silakan masukkan Nomor Kartu Anggota (Cardnumber) untuk melihat laporan kunjungan.';
        if ($cardnumber) {
            $borrowerInfo = M_vishistory::where('cardnumber', $cardnumber)->first();

            if ($borrowerInfo) {
                $dataKunjungan = M_vishistory::selectRaw('
                        EXTRACT(YEAR_MONTH FROM visittime) as tahun_bulan,
                        COUNT(id) as jumlah_kunjungan
                    ')
                    ->where('cardnumber', $cardnumber)
                    ->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'))
                    ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visittime)'), 'asc')
                    ->get();
                if ($dataKunjungan->isEmpty()) {
                    $pesan = 'Tidak ada data kunjungan ditemukan untuk Nomor Kartu Anggota: ' . $cardnumber . ' (' . $borrowerInfo->firstname . ').';
                } else {
                    $pesan = null;
                }
            } else {
                $pesan = 'Nomor Kartu Anggota (Cardnumber) tidak ditemukan.';
            }
        }

        return view('pages.kunjungan.cekKehadiran', compact('dataKunjungan', 'borrowerInfo', 'pesan', 'cardnumber'));
    }
}
