<?php

namespace App\Http\Controllers;

use App\Models\M_eprodi;
use App\Models\M_vishistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function totalStatistik()
    {
        // TOTAL statistik
        $totalJurnal = 100;
        $totalBuku = 100;
        $totalEksemplar = 100;
        $anggotaAktif = 100;
        $totalKunjungan = 100;

        // ============================ GRAFIK KUNJUNGAN TAHUNAN ============================ //
        // mengambil data kode prodi dan nama prodi
        $listProdi = M_eprodi::pluck('kode')->toArray();

        // mengambil data kunjungan prodi
        $thnDari = now()->year;
        // $thnDari = 2008;
        $data = M_vishistory::selectRaw('
            EXTRACT(YEAR_MONTH FROM visittime) as tahun_bulan,
            COUNT(visitorhistory.id) as jumlah_kunjungan
        ')
            ->leftJoin('borrowers as b', 'visitorhistory.cardnumber', '=', 'b.cardnumber')
            ->leftJoin('borrower_attributes as ba', 'ba.borrowernumber', '=', 'b.borrowernumber')
            ->leftJoin('authorised_values as av', function ($join) {
                $join->on('ba.code', '=', 'av.category')
                    ->on('ba.attribute', '=', 'av.authorised_value');
            })
            ->leftJoin('local_eprodi as le', 'le.kode', '=', 'av.authorised_value')
            ->where(DB::raw('YEAR(visitorhistory.visittime)'), $thnDari)
            ->whereIn('av.authorised_value', $listProdi)
            ->groupBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'av.authorised_value', 'le.nama')
            ->orderBy(DB::raw('EXTRACT(YEAR_MONTH FROM visitorhistory.visittime)'), 'asc')
            ->orderBy('av.authorised_value', 'asc')
            ->orderBy('le.nama', 'asc');
        // ============================ GRAFIK KUNJUNGAN TAHUNAN ============================ //

        return view('dashboard', compact('data', 'totalJurnal', 'totalBuku', 'totalEksemplar', 'anggotaAktif', 'totalKunjungan'));
    }
}
