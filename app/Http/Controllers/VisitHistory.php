<?php

namespace App\Http\Controllers;

use App\Models\M_borrowers;
use App\Models\M_eprodi;
use App\Models\M_vishistory;
use App\Models\VisitorHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VisitHistory extends Controller
{
    public function kunjunganProdi(Request $request)
    {

        // mengambil data kode prodi dan nama prodi
        $listProdi = M_eprodi::all();

        // mengambil data kunjungan prodi
        $kodeProdi = [$request->input('prodi', 'D400')];
        $thnDari = $request->input('tahun_awal', now()->year - 5);
        $thnSampai = $request->input('tahun_akhir', now()->year);
        $data = M_vishistory::selectRaw('
            EXTRACT(YEAR_MONTH FROM visittime) as tahun_bulan,
            av.authorised_value as kode_prodi,
            le.nama as nama_prodi,
            COUNT(visitorhistory.id) as jumlah_kunjungan
        ')
        ->leftJoin('borrowers as b', 'visitorhistory.cardnumber', '=', 'b.cardnumber')
        ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
        ->leftJoin('authorised_values as av', function($join) {
            $join->on('ba.code', '=', 'av.category')
                ->on('ba.attribute', '=', 'av.authorised_value');
        })
        ->leftJoin('local_eprodi as le', 'le.kode', '=', 'av.authorised_value')
        ->whereBetween(DB::raw('YEAR(visitorhistory.visittime)'), [$thnDari, $thnSampai])
        ->whereIn('av.authorised_value', $kodeProdi)
        ->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'av.authorised_value', 'le.nama')
        ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'asc')
        ->orderBy('av.authorised_value', 'asc')
        ->orderBy('le.nama', 'asc')
        ->paginate(10);

        return view('pages.kunjungan.prodi', compact('data', 'listProdi'));
    }

}
