<?php

namespace App\Http\Controllers;

use App\Models\M_eprodi;
use App\Models\M_items;
use Illuminate\Http\Request;
use App\Helpers\CnClassHelper;
use Carbon\Carbon;

class StatistikKoleksi extends Controller
{
    public function prosiding(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';
        $dataExists = false;

        if ($prodi) {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
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
                ->whereRaw('LEFT(items.itype,2) = "PR"'); // Filter khusus untuk prosiding

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->groupBy('Judul', 'Penerbit', 'Nomor', 'Kelas', 'TahunTerbit', 'Lokasi', 'Judul_b', 'Judul_c', 'Link');

            if ($request->has('export_csv')) {
                $dataUntukExport = $query->get();
                return $this->exportCsvProsiding($dataUntukExport, $namaProdi, $tahunTerakhir);
            } else {
                $data = $query->paginate(10);
                $data->appends($request->except('page'));
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.prosiding', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists'));
    }
    public function jurnal(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';
        $dataExists = false;

        if ($prodi) {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
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
                ->whereIn('items.itype', ['JR', 'JRA', 'EJ', 'JRT']) // Filter untuk jenis jurnal
                ->whereIn('bi.cn_class', $cnClasses);

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('RIGHT(items.enumchron, 4) >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->groupBy('Judul', 'Penerbit', 'Nomor', 'Kelas', 'Jenis', 'Lokasi');

            if ($request->has('export_csv')) {
                $dataUntukExport = $query->get();
                return $this->exportCsvJurnal($dataUntukExport, $namaProdi, $tahunTerakhir);
            } else {
                $data = $query->paginate(10);
                $data->appends($request->except('page'));
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.jurnal', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists'));
    }

    public function ebook(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';
        $dataExists = false;

        if ($prodi) {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
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
                ->whereIn('bi.cn_class', $cnClasses);

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->groupBy('Judul', 'Pengarang', 'Kota_Terbit', 'Penerbit', 'Tahun_Terbit', 'Lokasi');

            if ($request->has('export_csv')) {
                $dataUntukExport = $query->get();
                return $this->exportCsvEbook($dataUntukExport, $namaProdi, $tahunTerakhir);
            } else {
                $data = $query->paginate(10);
                $data->appends($request->except('page'));
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.ebook', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists'));
    }


    public function textbook(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';
        $dataExists = false;

        if ($prodi) {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
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
                ->whereIn('bi.cn_class', $cnClasses);

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->groupBy('Judul', 'Pengarang', 'Kota_Terbit', 'Penerbit', 'Tahun_Terbit', 'Lokasi');


            if ($request->has('export_csv')) {
                $dataUntukExport = $query->get();
                return $this->exportCsvTextbook($dataUntukExport, $namaProdi, $tahunTerakhir);
            } else {

                $data = $query->paginate(10);
                $data->appends($request->except('page'));
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.textbook', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists'));
    }

    public function periodikal(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';
        $dataExists = false;

        if ($prodi) {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $periodicalTypes = ['JR', 'JRA', 'MJA', 'MJI', 'MJIP', 'MJP'];

            $query = M_items::select('i.itype AS Jenis', 'bi.cn_class as Kelas', 'b.title AS Judul', 'i.enumchron AS Nomor')
                ->selectRaw('COUNT(i.itemnumber) AS Issue')
                ->selectRaw('SUM(i.copynumber) AS Eksemplar')
                ->addSelect('i.homebranch as Lokasi')
                ->from('items as i')
                ->join('biblioitems as bi', 'i.biblionumber', '=', 'bi.biblionumber')
                ->join('biblio as b', 'i.biblionumber', '=', 'b.biblionumber')
                ->where('i.itemlost', 0)
                ->where('i.withdrawn', 0)
                ->whereIn('i.itype', $periodicalTypes) // Filter untuk jenis periodikal
                ->whereIn('bi.cn_class', $cnClasses);

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->groupBy('Jenis', 'Judul', 'Nomor', 'Kelas', 'Lokasi');

            if ($request->has('export_csv')) {
                $dataUntukExport = $query->get();
                return $this->exportCsvPeriodikal($dataUntukExport, $namaProdi, $tahunTerakhir);
            } else {
                $data = $query->paginate(10);
                $data->appends($request->except('page'));
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.periodikal', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists'));
    }
    public function referensi(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');
        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';
        $dataExists = false;

        if ($prodi) {
            $prodiMapping = $listprodi->pluck('nama', 'kode')->toArray();
            $cnClasses = CnClassHelper::getCnClassByProdi($prodi);
            $namaProdi = $prodiMapping[$prodi] ?? 'Tidak Ditemukan';

            $query = M_items::selectRaw("
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
                ->whereIn('bi.cn_class', $cnClasses);

            if ($tahunTerakhir !== 'all') {
                $query->whereRaw('bi.publicationyear >= YEAR(CURDATE()) - ?', [$tahunTerakhir]);
            }

            $query->groupBy('Judul', 'Pengarang', 'Kota_Terbit', 'Penerbit', 'Tahun_Terbit', 'Kelas', 'Lokasi');

            if ($request->has('export_csv')) {
                $dataUntukExport = $query->get();
                return $this->exportCsvReferensi($dataUntukExport, $namaProdi, $tahunTerakhir);
            } else {
                $data = $query->paginate(10);
                $data->appends($request->except('page'));
                $dataExists = $data->isNotEmpty();
            }
        }

        return view('pages.dapus.referensi', compact('data', 'prodi', 'listprodi', 'namaProdi', 'tahunTerakhir', 'dataExists'));
    }

    public function koleksiPerprodi(Request $request)
    {
        $listprodi = M_eprodi::all();
        $prodi = $request->input('prodi');

        $tahunTerakhir = $request->input('tahun', 'all');

        $data = collect();
        $namaProdi = 'Pilih Program Studi';

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
        }

        return view('pages.dapus.prodi', compact('namaProdi', 'listprodi', 'data', 'prodi', 'tahunTerakhir'));
    }

    private function exportCsvJurnal($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_jurnal";

        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }

        if ($tahunTerakhir !== 'all') {
            $filename .= "_" . $tahunTerakhir . "_tahun_terakhir";
        } else {
            $filename .= "_semua_tahun";
        }

        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = ['Judul', 'Penerbit', 'Nomor', 'Kelas', 'Jenis', 'Lokasi', 'Issue', 'Eksemplar'];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->Judul,
                    $row->Penerbit,
                    $row->Nomor,
                    $row->Kelas,
                    $row->Jenis,
                    $row->Lokasi,
                    $row->Issue,
                    $row->Eksemplar
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportCsvReferensi($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_referensi";

        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }

        if ($tahunTerakhir !== 'all') {
            $filename .= "_" . $tahunTerakhir . "_tahun_terakhir";
        } else {
            $filename .= "_semua_tahun";
        }

        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'Judul',
            'Pengarang',
            'Penerbit',
            'Kota Terbit',
            'Tahun Terbit',
            'Kelas',
            'Lokasi',
            'Eksemplar'
        ];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->Judul,
                    $row->Pengarang,
                    $row->Penerbit,
                    $row->Kota_Terbit,
                    $row->Tahun_Terbit,
                    $row->Kelas,
                    $row->Lokasi,
                    $row->Eksemplar
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportCsvTextbook($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_textbook";

        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }

        if ($tahunTerakhir !== 'all') {
            $filename .= "_" . $tahunTerakhir . "_tahun_terakhir";
        } else {
            $filename .= "_semua_tahun";
        }

        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'Judul',
            'Pengarang',
            'Penerbit',
            'Kota Terbit',
            'Tahun Terbit',
            'Eksemplar',
            'Lokasi'
        ];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->Judul,
                    $row->Pengarang,
                    $row->Penerbit,
                    $row->Kota_Terbit,
                    $row->Tahun_Terbit,
                    $row->Eksemplar,
                    $row->Lokasi
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportCsvEbook($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_ebook";

        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }

        if ($tahunTerakhir !== 'all') {
            $filename .= "_" . $tahunTerakhir . "_tahun_terakhir";
        } else {
            $filename .= "_semua_tahun";
        }

        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'Judul',
            'Pengarang',
            'Kota Terbit',
            'Penerbit',
            'Tahun Terbit',
            'Eksemplar',
            'Lokasi'
        ];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->Judul,
                    $row->Pengarang,
                    $row->Kota_Terbit,
                    $row->Penerbit,
                    $row->Tahun_Terbit,
                    $row->Eksemplar,
                    $row->Lokasi
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportCsvPeriodikal($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_periodikal";
        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }

        if ($tahunTerakhir !== 'all') {
            $filename .= "_" . $tahunTerakhir . "_tahun_terakhir";
        } else {
            $filename .= "_semua_tahun";
        }
        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";
        $headers = [
            'Jenis',
            'Judul',
            'Nomor',
            'Kelas',
            'Lokasi',
            'Issue',
            'Eksemplar'
        ];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->Jenis,
                    $row->Judul,
                    $row->Nomor,
                    $row->Kelas,
                    $row->Lokasi,
                    $row->Issue,
                    $row->Eksemplar
                ], ';');
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function exportCsvProsiding($data, $namaProdi, $tahunTerakhir)
    {
        $filename = "koleksi_prosiding";

        if ($namaProdi && $namaProdi !== 'Pilih Program Studi') {
            $cleanProdiName = preg_replace('/[^a-zA-Z0-9 ]/', '', str_replace(' ', '_', $namaProdi));
            $filename .= "_" . $cleanProdiName;
        }

        if ($tahunTerakhir !== 'all') {
            $filename .= "_" . $tahunTerakhir . "_tahun_terakhir";
        } else {
            $filename .= "_semua_tahun";
        }

        $filename .= "_" . Carbon::now()->format('Ymd_His') . ".csv";

        $headers = [
            'Judul',
            'Kelas',
            'Penerbit',
            'Tahun Terbit',
            'Nomor',
            'Issue',
            'Eksemplar',
            'Lokasi',
            'Link'
        ];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, $headers, ';');

            foreach ($data as $row) {
                $fullJudul = $row->Judul;
                if (!empty($row->Judul_b)) {
                    $fullJudul .= ' : ' . $row->Judul_b;
                }
                if (!empty($row->Judul_c)) {
                    $fullJudul .= ' / ' . $row->Judul_c;
                }

                fputcsv($file, [
                    $fullJudul,
                    $row->Kelas,
                    $row->Penerbit,
                    $row->TahunTerbit,
                    $row->Nomor,
                    $row->Issue,
                    $row->Eksemplar,
                    $row->Lokasi,
                    $row->Link
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
