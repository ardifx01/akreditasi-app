<?php

namespace App\Http\Controllers;

use App\Models\M_eprodi;
use App\Models\M_items;
use Illuminate\Http\Request;
use App\Helpers\CnClassHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatistikKoleksi extends Controller
{
    /**
     * Tampilkan data koleksi prosiding dan tangani ekspor CSV.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function prosiding(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodiOptionAll = new M_eprodi();
        $prodiOptionAll->kode = 'all';
        $prodiOptionAll->nama = 'Semua Program Studi';
        $listprodi->prepend($prodiOptionAll);

        $prodi = $request->input('prodi', 'initial');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = '';
        $dataExists = false;
        $totalJudul = 0;
        $totalEksemplar = 0;

        if ($prodi && $prodi !== 'initial') {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
                bi.cn_class as Kelas,
                EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]') as Judul_a,
                EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]') as Judul_b,
                EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"c\"]') as Judul_c,
                b.author as Pengarang,
                bi.publishercode AS Penerbit,
                bi.publicationyear AS TahunTerbit,
                items.enumchron AS Nomor,
                CONCAT('https://search.lib.ums.ac.id/cgi-bin/koha/opac-detail.pl?biblionumber=', b.biblionumber) AS Link,
                COUNT(DISTINCT items.itemnumber) AS Issue,
                SUM(items.copynumber) AS Eksemplar,
                items.homebranch as Lokasi")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio_metadata as bm', 'b.biblionumber', '=', 'bm.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereRaw('LEFT(items.itype,2) = "PR"');

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $query->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->orderBy('TahunTerbit', 'desc');
            $query->groupBy('Judul_a', 'Judul_b', 'Judul_c', 'Pengarang', 'Penerbit', 'Nomor', 'Kelas', 'TahunTerbit', 'Lokasi', 'Link');

            $processedData = $query->get()->map(function ($row) {
                $fullJudul = $row->Judul_a;
                if (!empty($row->Judul_b)) {
                    $fullJudul .= ' : ' . $row->Judul_b;
                }
                if (!empty($row->Judul_c)) {
                    $fullJudul .= ' / ' . $row->Judul_c;
                }

                $row->Judul = html_entity_decode($fullJudul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Penerbit = html_entity_decode($row->Penerbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Pengarang = html_entity_decode($row->Pengarang, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return $row;
            });
            $totalQuery = M_items::selectRaw("
            COUNT(DISTINCT b.biblionumber) as total_judul,
            SUM(items.copynumber) as total_eksemplar
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereIn('items.itype', ['PR']);


            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $totalQuery->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $totalQuery->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $totals = $totalQuery->first();
            $totalJudul = $totals->total_judul ?? 0;
            $totalEksemplar = $totals->total_eksemplar ?? 0;

            if ($request->has('export_csv')) {
                return $this->exportCsvProsiding($processedData, $namaProdi, $tahunTerakhir);
            } else {
                $data = $processedData;
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.prosiding', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists', 'totalJudul', 'totalEksemplar'));
    }

    /**
     * Tampilkan data koleksi jurnal dan tangani ekspor CSV.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function jurnal(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodiOptionAll = new M_eprodi();
        $prodiOptionAll->kode = 'all';
        $prodiOptionAll->nama = 'Semua Program Studi';
        $listprodi->prepend($prodiOptionAll);

        $prodi = $request->input('prodi', 'initial');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = '';
        $dataExists = false;
        $totalJudul = 0;
        $totalEksemplar = 0;

        if ($prodi && $prodi !== 'initial') {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
            bi.cn_class as Kelas,
            EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]') as Judul_a,
            EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]') as Judul_b,
            MAX(bi.publishercode) AS Penerbit_Raw,
            MAX(bi.place) AS Place_Raw,
            MAX(CONCAT(COALESCE(bi.publishercode,''), ' ', COALESCE(bi.place,''))) AS Penerbit,
            items.enumchron AS Nomor,
            SUM(items.copynumber) AS Eksemplar,
            MAX(bi.publicationyear) as tahun_terbit,
            COUNT(DISTINCT items.itemnumber) AS Issue,
            CONCAT('https://search.lib.ums.ac.id/cgi-bin/koha/opac-detail.pl?biblionumber=', b.biblionumber) AS Link,
            i1.description as Jenis,
            items.homebranch as Lokasi,
            MAX(items.biblionumber) as biblionumber
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio_metadata as bm', 'b.biblionumber', '=', 'bm.biblionumber')
                ->join('itemtypes as i1', 'i1.itemtype', '=', 'items.itype')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereIn('items.itype', ['JR', 'JRA', 'EJ', 'JRT']);
            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $query->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->orderBy('tahun_terbit', 'desc');
            $query->orderBy('Judul_a', 'asc');

            $query->groupBy('Judul_a', 'Judul_b', 'Nomor', 'Kelas', 'Jenis', 'Link', 'Lokasi');

            $processedData = $query->get()->map(function ($row) {
                $fullJudul = $row->Judul_a;
                if (!empty($row->Judul_b)) {
                    $fullJudul .= ' ' . $row->Judul_b;
                }

                $row->Judul = html_entity_decode($fullJudul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Penerbit = html_entity_decode($row->Penerbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return $row;
            });

            // Query untuk total judul dan eksemplar
            $totalQuery = M_items::selectRaw("
            COUNT(DISTINCT b.biblionumber) as total_judul,
            SUM(items.copynumber) as total_eksemplar
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereIn('items.itype', ['JR', 'JRA', 'EJ', 'JRT']);

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $totalQuery->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $totalQuery->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $totals = $totalQuery->first();
            $totalJudul = $totals->total_judul ?? 0;
            $totalEksemplar = $totals->total_eksemplar ?? 0;

            if ($request->has('export_csv')) {
                return $this->exportCsvJurnal($processedData, $namaProdi, $tahunTerakhir);
            } else {
                $data = $processedData;
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.jurnal', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists', 'totalJudul', 'totalEksemplar'));
    }

    /**
     * Tampilkan data koleksi e-book dan tangani ekspor CSV.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function ebook(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodiOptionAll = new M_eprodi();
        $prodiOptionAll->kode = 'all';
        $prodiOptionAll->nama = 'Semua Program Studi';
        $listprodi->prepend($prodiOptionAll);

        $prodi = $request->input('prodi', 'initial');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = '';
        $dataExists = false;

        // ⭐ Deklarasikan variabel dengan nilai default di luar blok if
        $totalJudul = 0;
        $totalEksemplar = 0;

        if ($prodi && $prodi !== 'initial') {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
            EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]') as Judul_a,
            EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]') as Judul_b,
            b.author as Pengarang,
            bi.place AS Kota_Terbit,
            MAX(bi.publishercode) AS Penerbit_Raw,
            MAX(bi.place) AS Place_Raw,
            MAX(CONCAT(COALESCE(bi.publishercode,''), ' ', COALESCE(bi.place,''))) AS Penerbit,
            bi.publicationyear AS Tahun_Terbit,
            COUNT(items.itemnumber) AS Eksemplar,
            MAX(items.biblionumber) as biblionumber
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio_metadata as bm', 'b.biblionumber', '=', 'bm.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->where('items.itype', 'EB');

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $query->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->orderBy('Tahun_Terbit', 'desc');
            $query->groupBy('Judul_a', 'Judul_b', 'Pengarang', 'Kota_Terbit', 'Tahun_Terbit');

            $processedData = $query->get()->map(function ($row) {
                $fullJudul = $row->Judul_a;
                if (!empty($row->Judul_b)) {
                    $fullJudul .= ' ' . $row->Judul_b;
                }

                $row->Judul = html_entity_decode($fullJudul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Pengarang = html_entity_decode($row->Pengarang, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Penerbit = html_entity_decode($row->Penerbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Kota_Terbit = html_entity_decode($row->Kota_Terbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return $row;
            });

            $totalQuery = M_items::selectRaw("
            COUNT(DISTINCT b.biblionumber) as total_judul,
            COUNT(items.itemnumber) as total_eksemplar
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereIn('items.itype', ['EB']);

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $totalQuery->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $totalQuery->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $totals = $totalQuery->first();
            // ⭐ Di sini, variabel akan di-overwrite jika query berhasil
            $totalJudul = $totals->total_judul ?? 0;
            $totalEksemplar = $totals->total_eksemplar ?? 0;

            if ($request->has('export_csv')) {
                return $this->exportCsvEbook($processedData, $namaProdi, $tahunTerakhir);
            } else {
                $data = $processedData;
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.ebook', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists', 'totalJudul', 'totalEksemplar'));
    }

    /**
     * Tampilkan data koleksi textbook dan tangani ekspor CSV.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function textbook(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodiOptionAll = new M_eprodi();
        $prodiOptionAll->kode = 'all';
        $prodiOptionAll->nama = 'Semua Program Studi';
        $listprodi->prepend($prodiOptionAll);

        $prodi = $request->input('prodi', 'initial');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = '';
        $dataExists = false;

        // ⭐ Tambahkan inisialisasi variabel di sini
        $totalJudul = 0;
        $totalEksemplar = 0;

        if ($prodi && $prodi !== 'initial') {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
            EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]') as Judul_a,
            EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]') as Judul_b,
            b.author as Pengarang,
            bi.place AS Kota_Terbit,
            MAX(bi.publishercode) AS Penerbit_Raw,
            MAX(bi.place) AS Place_Raw,
            MAX(CONCAT(COALESCE(bi.publishercode,''), ' ', COALESCE(bi.place,''))) AS Penerbit,
            bi.publicationyear AS Tahun_Terbit,
            COUNT(items.itemnumber) AS Eksemplar,
            items.homebranch as Lokasi
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio_metadata as bm', 'b.biblionumber', '=', 'bm.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereRaw('LEFT(items.itype, 3) = "BKS"')
                ->whereRaw('LEFT(items.ccode, 1) <> "R"');

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $query->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }
            $query->orderBy('Tahun_Terbit', 'desc');
            $query->groupBy('Judul_a', 'Judul_b', 'Pengarang', 'Kota_Terbit', 'Tahun_Terbit', 'Lokasi');

            $processedData = $query->get()->map(function ($row) {
                $fullJudul = $row->Judul_a;
                if (!empty($row->Judul_b)) {
                    $fullJudul .= ' ' . $row->Judul_b;
                }

                $row->Judul = html_entity_decode($fullJudul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Pengarang = html_entity_decode($row->Pengarang, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Penerbit = html_entity_decode($row->Penerbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Kota_Terbit = html_entity_decode($row->Kota_Terbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return $row;
            });

            $totalQuery = M_items::selectRaw("
            COUNT(DISTINCT b.biblionumber) as total_judul,
            COUNT(items.itemnumber) as total_eksemplar
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereRaw('LEFT(items.itype, 3) = "BKS"')
                ->whereRaw('LEFT(items.ccode, 1) <> "R"');

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $totalQuery->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $totalQuery->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $totals = $totalQuery->first();
            // ⭐ Pastikan variabel ini didefinisikan dengan nilai dari query
            $totalJudul = $totals->total_judul ?? 0;
            $totalEksemplar = $totals->total_eksemplar ?? 0;

            if ($request->has('export_csv')) {
                return $this->exportCsvTextbook($processedData, $namaProdi, $tahunTerakhir);
            } else {
                $data = $processedData;
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.textbook', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists', 'totalJudul', 'totalEksemplar'));
    }

    /**
     * Tampilkan data koleksi periodikal dan tangani ekspor CSV.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function periodikal(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodiOptionAll = new M_eprodi();
        $prodiOptionAll->kode = 'all';
        $prodiOptionAll->nama = 'Semua Program Studi';
        $listprodi->prepend($prodiOptionAll);

        $prodi = $request->input('prodi', 'initial');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = '';
        $dataExists = false;

        if ($prodi && $prodi !== 'initial') {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $periodicalTypes = ['JR', 'JRA', 'MJA', 'MJI', 'MJIP', 'MJP'];

            $query = M_items::select(
                'i.itype AS Jenis_kode',
                't.description AS Jenis',
                'bi.publishercode AS Penerbit',
                'bi.place AS Tempat_Terbit',
                'bi.cn_class as Kelas',
                'b.title AS Judul',
                'i.enumchron AS Nomor',
                'i.homebranch as Lokasi'
            )
                ->selectRaw('COUNT(i.itemnumber) AS Issue')
                ->selectRaw('SUM(i.copynumber) AS Eksemplar')
                ->from('items as i')
                ->join('biblioitems as bi', 'i.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'i.biblionumber', '=', 'b.biblionumber')
                ->join('itemtypes as t', 'i.itype', '=', 't.itemtype')
                ->where('i.itemlost', 0)
                ->where('i.withdrawn', 0)
                ->whereIn('i.itype', $periodicalTypes);

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $query->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->groupBy('Jenis_kode', 'Jenis', 'Judul', 'Nomor', 'Kelas', 'Lokasi', 'Penerbit', 'Tempat_Terbit');

            $processedData = $query->get()->map(function ($row) {
                $row->Judul = html_entity_decode($row->Judul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $penerbit = $row->Penerbit;
                if (!empty($row->Tempat_Terbit)) {
                    $penerbit .= ' : ' . $row->Tempat_Terbit;
                }
                $row->Penerbit_Lengkap = html_entity_decode($penerbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                return $row;
            });

            $totalQuery = M_items::selectRaw("
            COUNT(DISTINCT b.biblionumber) as total_judul,
            COUNT(items.itemnumber) as total_eksemplar
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereIn('items.itype', $periodicalTypes);

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $totalQuery->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $totalQuery->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $totals = $totalQuery->first();
            // ⭐ Pastikan variabel ini didefinisikan dengan nilai dari query
            $totalJudul = $totals->total_judul ?? 0;
            $totalEksemplar = $totals->total_eksemplar ?? 0;

            if ($request->has('export_csv')) {
                return $this->exportCsvPeriodikal($processedData, $namaProdi, $tahunTerakhir);
            } else {
                $data = $processedData;
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.periodikal', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists', 'totalJudul', 'totalEksemplar'));
    }

    /**
     * Tampilkan data koleksi referensi dan tangani ekspor CSV.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function referensi(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodiOptionAll = new M_eprodi();
        $prodiOptionAll->kode = 'all';
        $prodiOptionAll->nama = 'Semua Program Studi';
        $listprodi->prepend($prodiOptionAll);

        $prodi = $request->input('prodi', 'initial');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = '';
        $dataExists = false;
        $totalJudul = 0;
        $totalEksemplar = 0;

        if ($prodi && $prodi !== 'initial') {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
                bi.cn_class as Kelas,
                EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]') as Judul_a,
                EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]') as Judul_b,
                b.author as Pengarang,
                bi.place AS Kota_Terbit,
                MAX(bi.publishercode) AS Penerbit_Raw,
                MAX(bi.place) AS Place_Raw,
                MAX(CONCAT(COALESCE(bi.publishercode,''), ' ', COALESCE(bi.place,''))) AS Penerbit,
                bi.publicationyear AS Tahun_Terbit,
                COUNT(i.itemnumber) AS Eksemplar,
                i.homebranch as Lokasi
            ")
                ->from('items as i')
                ->join('biblioitems as bi', 'i.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'i.biblionumber', '=', 'b.biblionumber')
                ->join('biblio_metadata as bm', 'b.biblionumber', '=', 'bm.biblionumber')
                ->where('i.itemlost', 0)
                ->where('i.withdrawn', 0)
                ->whereRaw('LEFT(i.itype,3) = "BKS"')
                ->whereRaw('LEFT(i.ccode,1) = "R"');

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $query->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->orderBy('Tahun_Terbit', 'desc');
            $query->groupBy('Judul_a', 'Judul_b', 'Pengarang', 'Kota_Terbit', 'Tahun_Terbit', 'Kelas', 'Lokasi');

            $processedData = $query->get()->map(function ($row) {
                $fullJudul = $row->Judul_a;
                if (!empty($row->Judul_b)) {
                    $fullJudul .= ' ' . $row->Judul_b;
                }

                $row->Judul = html_entity_decode($fullJudul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Pengarang = html_entity_decode($row->Pengarang, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Penerbit = html_entity_decode($row->Penerbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $row->Kota_Terbit = html_entity_decode($row->Kota_Terbit, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                return $row;
            });

            $totalQuery = M_items::selectRaw("
            COUNT(DISTINCT b.biblionumber) as total_judul,
            COUNT(items.itemnumber) as total_eksemplar
        ")
                ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
                ->where('items.itemlost', 0)
                ->where('items.withdrawn', 0)
                ->whereRaw('LEFT(items.itype,3) = "BKS"')
                ->whereRaw('LEFT(items.ccode,1) = "R"');

            if ($prodi !== 'all') {
                $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
                $totalQuery->whereIn('bi.cn_class', $cnClasses);
            }

            if ($tahunTerakhir !== 'all') {
                $totalQuery->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $totals = $totalQuery->first();
            // ⭐ Pastikan variabel ini didefinisikan dengan nilai dari query
            $totalJudul = $totals->total_judul ?? 0;
            $totalEksemplar = $totals->total_eksemplar ?? 0;

            if ($request->has('export_csv')) {
                return $this->exportCsvReferensi($processedData, $namaProdi, $tahunTerakhir);
            } else {
                $data = $processedData;
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.referensi', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists', 'totalJudul', 'totalEksemplar'));
    }


    /**
     * Tampilkan data koleksi per prodi.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function koleksiPerprodi(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';
        $chartData = [];

        if ($prodi) {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::select('t.description AS Jenis', 'i.ccode AS Koleksi')
                ->selectRaw('COUNT(DISTINCT i.biblionumber) AS Judul')
                ->selectRaw('COUNT(i.itemnumber) AS Eksemplar')
                ->from('items as i')
                ->join('biblioitems as bi', 'i.biblionumber', '=', 'bi.biblionumber')
                ->join('itemtypes as t', 'i.itype', '=', 't.itemtype')
                ->where('i.itemlost', 0)
                ->where('i.withdrawn', 0)
                ->whereIn('bi.cn_class', $cnClasses);

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $data = $query->groupBy('Jenis', 'Koleksi')
                ->orderBy('Jenis', 'asc')
                ->orderBy('Koleksi', 'asc')
                ->get();
            $chartData = $data->map(function ($item) {
                return [
                    'jenis' => $item->Jenis,
                    'judul' => $item->Judul,
                    'eksemplar' => $item->Eksemplar
                ];
            })->values()->all();
        }

        return view('pages.dapus.prodi', compact('namaProdi', 'listprodi', 'data', 'prodi', 'tahunTerakhir', 'chartData'));
    }

    /**
     * Tampilkan detail koleksi per prodi.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailKoleksi(Request $request)
    {
        $prodi = $request->input('prodi');
        $jenis = $request->input('jenis');
        $tahunTerakhir = $request->input('tahun', 'all');
        $page = $request->input('page', 1);
        $perPage = 10;

        $detailData = collect();

        if ($prodi && $jenis) {
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);

            $query = M_items::select(
                'b.title AS Judul',
                DB::raw('MIN(bi.cn_class) AS Kelas'),
                DB::raw('MIN(bi.publicationyear) AS TahunTerbit'),
                DB::raw('SUM(CASE WHEN i.itemlost = 0 AND i.withdrawn = 0 THEN 1 ELSE 0 END) AS Eksemplar'),
                DB::raw('GROUP_CONCAT(DISTINCT i.homebranch ORDER BY i.homebranch SEPARATOR ", ") AS Lokasi')
            )
                ->from('items as i')
                ->join('biblioitems as bi', 'i.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'i.biblionumber', '=', 'b.biblionumber')
                ->join('itemtypes as t', 'i.itype', '=', 't.itemtype')
                ->where('i.itemlost', 0)
                ->where('i.withdrawn', 0)
                ->whereIn('bi.cn_class', $cnClasses)
                ->where('t.description', $jenis);

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $detailData = $query->groupBy('b.title')
                ->orderBy('Kelas', 'asc')
                ->paginate($perPage, ['*'], 'page', $page);
        }

        return response()->json($detailData);
    }

    /**
     * Ekspor data jurnal ke format CSV.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $namaProdi
     * @param string $tahunTerakhir
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsvJurnal($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_jurnal";
        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }
        $filename .= "_" . ($tahunTerakhir !== 'all' ? $tahunTerakhir . "_tahun_terakhir" : "semua_tahun");
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = ['No', 'Judul', 'Penerbit', 'Nomor', 'Eksemplar', 'Jenis', 'Lokasi'];

        $callback = function () use ($data, $headers, $namaProdi, $tahunTerakhir) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            $judulProdi = 'Jurnal - ' . ($namaProdi ?: 'Semua Program Studi');
            $judulTahun = ($tahunTerakhir !== 'all') ? ('Tahun Terbit: ' . $tahunTerakhir . ' terakhir') : 'Semua Tahun Terbit';
            fputcsv($file, [$judulProdi . ' - ' . $judulTahun], ';');
            fputcsv($file, $headers, ';');

            $i = 1;
            foreach ($data as $row) {
                $rowData = [
                    $i++,
                    $row->Judul,
                    $row->Penerbit,
                    $row->Nomor,
                    (int) $row->Eksemplar,
                    $row->Jenis,
                    $row->Lokasi,
                ];
                fputcsv($file, $rowData, ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Ekspor data referensi ke format CSV.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $namaProdi
     * @param string $tahunTerakhir
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsvReferensi($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_referensi";
        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }
        $filename .= "_" . ($tahunTerakhir !== 'all' ? $tahunTerakhir . "_tahun_terakhir" : "semua_tahun");
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'No',
            'Judul',
            'Pengarang',
            'Penerbit',
            'Tahun Terbit',
            'Eksemplar',
            'Lokasi',
        ];

        $callback = function () use ($data, $headers, $namaProdi, $tahunTerakhir) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            $judulProdi = 'Referensi - ' . ($namaProdi ?: 'Semua Program Studi');
            $judulTahun = ($tahunTerakhir !== 'all') ? ('Tahun Terbit: ' . $tahunTerakhir . ' terakhir') : 'Semua Tahun Terbit';
            fputcsv($file, [$judulProdi . ' - ' . $judulTahun], ';');
            fputcsv($file, $headers, ';');

            $i = 1;
            foreach ($data as $row) {
                $rowData = [
                    $i++,
                    $row->Judul,
                    $row->Pengarang,
                    $row->Penerbit,
                    (int) $row->Tahun_Terbit,
                    (int) $row->Eksemplar,
                    $row->Lokasi
                ];
                fputcsv($file, $rowData, ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Ekspor data textbook ke format CSV.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $namaProdi
     * @param string $tahunTerakhir
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsvTextbook($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_textbook";
        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }
        $filename .= "_" . ($tahunTerakhir !== 'all' ? $tahunTerakhir . "_tahun_terakhir" : "semua_tahun");
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'No',
            'Judul',
            'Pengarang',
            'Kota Terbit',
            'Penerbit',
            'Tahun Terbit',
            'Eksemplar',
            'Lokasi'
        ];

        $callback = function () use ($data, $headers, $namaProdi, $tahunTerakhir) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            $judulProdi = 'Buku Teks - ' . ($namaProdi ?: 'Semua Program Studi');
            $judulTahun = ($tahunTerakhir !== 'all') ? ('Tahun Terbit: ' . $tahunTerakhir . ' terakhir') : 'Semua Tahun Terbit';
            fputcsv($file, [$judulProdi . ' - ' . $judulTahun], ';');
            fputcsv($file, $headers, ';');

            $i = 1;
            foreach ($data as $row) {
                $rowData = [
                    $i++,
                    $row->Judul,
                    $row->Pengarang,
                    $row->Kota_Terbit,
                    $row->Penerbit,
                    (int) $row->Tahun_Terbit,
                    (int) $row->Eksemplar,
                    $row->Lokasi
                ];
                fputcsv($file, $rowData, ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Ekspor data e-book ke format CSV.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $namaProdi
     * @param string $tahunTerakhir
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsvEbook($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_ebook";
        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }
        $filename .= "_" . ($tahunTerakhir !== 'all' ? $tahunTerakhir . "_tahun_terakhir" : "semua_tahun");
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'No',
            'Judul',
            'Pengarang',
            'Kota Terbit',
            'Penerbit',
            'Tahun Terbit',
            'Eksemplar',
        ];

        $callback = function () use ($data, $headers, $namaProdi, $tahunTerakhir) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            $judulProdi = 'E-Book - ' . ($namaProdi ?: 'Semua Program Studi');
            $judulTahun = ($tahunTerakhir !== 'all') ? ('Tahun Terbit: ' . $tahunTerakhir . ' terakhir') : 'Semua Tahun Terbit';
            fputcsv($file, [$judulProdi . ' - ' . $judulTahun], ';');
            fputcsv($file, $headers, ';');

            $i = 1;
            foreach ($data as $row) {
                $rowData = [
                    $i++,
                    $row->Judul,
                    $row->Pengarang,
                    $row->Kota_Terbit,
                    $row->Penerbit,
                    (int) $row->Tahun_Terbit,
                    (int) $row->Eksemplar,
                ];
                fputcsv($file, $rowData, ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Ekspor data prosiding ke format CSV.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $namaProdi
     * @param string $tahunTerakhir
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsvProsiding($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_prosiding";
        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }
        $filename .= "_" . ($tahunTerakhir !== 'all' ? $tahunTerakhir . "_tahun_terakhir" : "semua_tahun");
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'No',
            'Judul',
            'Author',
            'Penerbit',
            'Tahun Terbit',
            // 'Nomor',
            'Jumlah',
            'Lokasi',
            // 'Link'
        ];

        $callback = function () use ($data, $headers, $namaProdi, $tahunTerakhir) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            $judulProdi = 'Prosiding - ' . ($namaProdi ?: 'Semua Program Studi');
            $judulTahun = ($tahunTerakhir !== 'all') ? ('Tahun Terbit: ' . $tahunTerakhir . ' terakhir') : 'Semua Tahun Terbit';
            fputcsv($file, [$judulProdi . ' - ' . $judulTahun], ';');
            fputcsv($file, $headers, ';');

            $i = 1;
            foreach ($data as $row) {
                $rowData = [
                    $i++,
                    $row->Judul,
                    $row->Pengarang,
                    $row->Penerbit,
                    (int) $row->TahunTerbit,
                    // $row->Nomor,
                    (int) $row->Eksemplar,
                    $row->Lokasi,
                    // $row->Link
                ];
                fputcsv($file, $rowData, ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Ekspor data periodikal ke format CSV.
     *
     * @param \Illuminate\Support\Collection $data
     * @param string $namaProdi
     * @param string $tahunTerakhir
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    private function exportCsvPeriodikal($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_periodikal";
        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }
        $filename .= "_" . ($tahunTerakhir !== 'all' ? $tahunTerakhir . "_tahun_terakhir" : "semua_tahun");
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = ['No', 'Jenis', 'Judul', 'Nomor', 'Issue', 'Eksemplar', 'Lokasi'];

        $callback = function () use ($data, $headers, $namaProdi, $tahunTerakhir) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            $judulProdi = 'Periodikal - ' . ($namaProdi ?: 'Semua Program Studi');
            $judulTahun = ($tahunTerakhir !== 'all') ? ('Tahun Terbit: ' . $tahunTerakhir . ' terakhir') : 'Semua Tahun Terbit';
            fputcsv($file, [$judulProdi . ' - ' . $judulTahun], ';');
            fputcsv($file, $headers, ';');

            $i = 1;
            foreach ($data as $row) {
                $rowData = [
                    $i++,
                    $row->Jenis,
                    $row->Judul,
                    $row->Nomor,
                    (int) $row->Issue,
                    (int) $row->Eksemplar,
                    $row->Lokasi,
                ];
                fputcsv($file, $rowData, ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
