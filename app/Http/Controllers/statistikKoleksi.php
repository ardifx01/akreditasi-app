<?php

namespace App\Http\Controllers;

use App\Models\M_eprodi;
use App\Models\M_items;
use Illuminate\Http\Request;
use App\Helpers\CnClassHelper;


class StatistikKoleksi extends Controller
{
    public function prosiding(Request $request){
        $listprodi = M_eprodi::all(); //mengambil list prodi dari tabel
        $prodi = $request->input('prodi', 'L200'); //defailt value
        $cnClasses = CnClassHelper::getCnClassByProdi($prodi); //mengambil cn_class berdasarkan prodi di helper
        $namaProdi = $listProdi[$prodi] ?? 'Semua Prodi';

        $data = M_items::selectRaw("
        bi.cn_class as Kelas,
        EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]') as Judul,
        CONCAT(EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]'),' ',EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]')) as Judul_b,
        EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"c\"]') as Judul_c,
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
        ->whereIn('bi.cn_class', $cnClasses)
        ->where('items.itemlost', 0)
        ->where('items.withdrawn', 0)
        ->whereRaw('LEFT(items.itype,2) = "PR"')
        ->groupBy('Judul', 'Penerbit', 'Nomor', 'Kelas', 'TahunTerbit', 'Lokasi', 'Judul_b', 'Judul_c', 'Link')
        ->paginate(10);
        $data = $data->appends($request->all());

        return view('pages.dapus.prosiding', compact('data', 'prodi', 'listprodi', 'namaProdi'));
    }

    public function jurnal(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi', 'L200');
        $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
        $namaProdi = $listprodi[$prodi] ?? 'Semua Prodi';

        
        $tahunTerakhir = $request->input('tahun', 5);

        $data = M_items::selectRaw("
            bi.cn_class as Kelas,
            CONCAT(EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]'),' ',EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]')) as Judul,
            bi.publishercode AS Penerbit,
            items.enumchron AS Nomor,
            COUNT(DISTINCT items.itemnumber) AS Issue,
            SUM(items.copynumber) AS Eksemplar,
            i1.description as Jenis,
            items.homebranch as Lokasi
        ")
        ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
        ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
        ->join('biblio_metadata as bm', 'b.biblionumber', '=', 'bm.biblionumber')
        ->join('itemtypes as i1', 'i1.itemtype', '=', 'items.itype')
        ->where('items.itemlost', 0)
        ->where('items.withdrawn', 0)
        ->whereIn('items.itype', ['JR', 'JRA', 'EJ', 'JRT'])
        ->whereIn('bi.cn_class', $cnClasses)
        ->whereRaw('RIGHT(items.enumchron, 4) >= YEAR(CURDATE()) - ?', [$tahunTerakhir])
        ->groupBy('Judul', 'Penerbit', 'Nomor', 'Kelas', 'Jenis', 'Lokasi')
        ->paginate(10);

        $data = $data->appends($request->all());

        return view('pages.dapus.jurnal', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir'));
    }

    public function ebook(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi', 'L200');
        $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
        $namaProdi = $listprodi[$prodi] ?? 'Semua Prodi';

        $data = M_items::selectRaw("
            CONCAT(EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]'),' ',EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]')) as Judul,
            b.author as Pengarang,
            bi.place AS Kota_Terbit,
            bi.publishercode AS Penerbit,
            bi.publicationyear AS Tahun_Terbit,
            COUNT(items.itemnumber) AS Eksemplar,
            items.homebranch as Lokasi
        ")
        ->join('biblioitems as bi', 'items.biblionumber', '=', 'bi.biblionumber')
        ->join('biblio as b', 'b.biblionumber', '=', 'bi.biblionumber')
        ->join('biblio_metadata as bm', 'b.biblionumber', '=', 'bm.biblionumber')
        ->where('items.itemlost', 0)
        ->where('items.withdrawn', 0)
        ->where('items.itype', 'EB')
        ->whereIn('bi.cn_class', $cnClasses)
        ->groupBy('Judul', 'Pengarang', 'Kota_Terbit', 'Penerbit', 'Tahun_Terbit', 'Lokasi')
        ->paginate(10);

        $data = $data->appends($request->all());

        return view('pages.dapus.ebook', compact('data', 'prodi', 'listprodi', 'namaProdi'));
    }

    public function textbook(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi', 'L200');
        $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
        $namaProdi = $listprodi[$prodi] ?? 'Semua Prodi';

        $data = M_items::selectRaw("
            CONCAT(EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]'),' ',EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]')) as Judul,
            b.author as Pengarang,
            bi.place AS Kota_Terbit,
            bi.publishercode AS Penerbit,
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
        ->whereRaw('LEFT(items.ccode, 1) <> "R"')
        ->whereIn('bi.cn_class', $cnClasses)
        ->groupBy('Judul', 'Pengarang', 'Kota_Terbit', 'Penerbit', 'Tahun_Terbit', 'Lokasi')
        ->paginate(10);

        $data = $data->appends($request->all());

        return view('pages.dapus.textbook', compact('data', 'prodi', 'listprodi', 'namaProdi'));
    }

    public function periodikal(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi', 'L200');
        $cnClass = CnClassHelper::getCnClassByProdi($prodi);
        $namaProdi = $listprodi[$prodi] ?? 'Semua Prodi';

        $periodicalTypes = ['JR', 'JRA', 'MJA', 'MJI', 'MJIP', 'MJP'];

        $data = M_items::select('i.itype AS Jenis', 'bi.cn_class as Kelas', 'b.title AS Judul', 'i.enumchron AS Nomor')
            ->selectRaw('COUNT(i.itemnumber) AS Issue')
            ->selectRaw('SUM(i.copynumber) AS Eksemplar')
            ->addSelect('i.homebranch as Lokasi')
            ->from('items as i')
            ->join('biblioitems as bi', 'i.biblionumber', '=', 'bi.biblionumber')
            ->join('biblio as b', 'i.biblionumber', '=', 'b.biblionumber')
            ->where('i.itemlost', 0)
            ->where('i.withdrawn', 0)
            ->whereIn('i.itype', $periodicalTypes)
            ->whereIn('bi.cn_class', $cnClass)
            ->groupBy('Jenis', 'Judul', 'Nomor', 'Kelas', 'Lokasi')
            ->paginate(10);

        $data = $data->appends($request->all());

        return view('pages.dapus.periodikal', compact('data', 'prodi', 'listprodi', 'namaProdi'));
    }

    public function referensi(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi', 'L200');
        $cnClass = CnClassHelper::getCnClassByProdi($prodi);
        $namaProdi = $listprodi[$prodi] ?? 'Semua Prodi';

        $data = M_items::selectRaw("
            bi.cn_class as Kelas,
            CONCAT(EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"a\"]'),' ',EXTRACTVALUE(bm.metadata,'//datafield[@tag=\"245\"]/subfield[@code=\"b\"]')) as Judul,
            b.author as Pengarang,
            bi.place AS Kota_Terbit,
            bi.publishercode AS Penerbit,
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
        ->whereRaw('LEFT(i.ccode,1) = "R"')
        ->whereIn('bi.cn_class', $cnClass)
        ->groupBy('Judul', 'Pengarang', 'Kota_Terbit', 'Penerbit', 'Tahun_Terbit', 'Kelas', 'Lokasi')
        ->paginate(10);

        $data = $data->appends($request->all());

        return view('pages.dapus.referensi', compact('data', 'prodi', 'listprodi', 'namaProdi'));
    }

    public function koleksiPerprodi(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi', 'L200');
        $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
        $namaProdi = $listprodi[$prodi] ?? 'Semua Prodi';
        $startDate = $request->input('start_date', now()->subYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $data = M_items::select('t.description AS Jenis', 'i.ccode AS Koleksi')
            ->selectRaw('COUNT(DISTINCT i.biblionumber) AS Judul')
            ->selectRaw('COUNT(i.itemnumber) AS Eksemplar')
            ->from('items as i')
            ->join('biblioitems as bi', 'i.biblionumber', '=', 'bi.biblionumber')
            ->join('itemtypes as t', 'i.itype', '=', 't.itemtype')
            ->where('i.itemlost', 0)
            ->whereBetween('i.replacementpricedate', [$startDate, $endDate])
            ->whereIn('bi.cn_class', $cnClasses)
            ->groupBy('Jenis', 'Koleksi')
            ->orderBy('Jenis', 'asc')
            ->orderBy('Koleksi', 'asc')
            ->get();

        // Mengirimkan data dan parameter filter ke view
        return view('pages.dapus.prodi', compact('namaProdi', 'listprodi', 'data', 'startDate', 'endDate'));
    }



}
