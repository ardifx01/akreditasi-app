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
        ->groupBy('Judul', 'Penerbit', 'Nomor', 'Kelas', 'TahunTerbit', 'Lokasi', 'Judul_b', 'Judul_c')
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
}
