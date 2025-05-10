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
}
