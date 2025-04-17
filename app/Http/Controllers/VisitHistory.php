<?php

namespace App\Http\Controllers;

use App\Models\M_vishistory;
use App\Models\VisitorHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class VisitHistory extends Controller
{
    public function index(Request $request)
    {
        // Rentang tahun
        $fromYear = $request->input('from_year', date('Y') - 1);
        $toYear = $request->input('to_year', date('Y'));

        $results = M_vishistory::with(['borrowers' => function ($query) {
                $query->select('cardnumber');
            }])
            ->select(
                DB::raw("year(visittime) AS year"),
                DB::raw("COUNT(DISTINCT DATE(visittime), cardnumber) AS total_visits")
            )
            ->whereYear('visittime', '>=', $fromYear)
            ->whereYear('visittime', '<=', $toYear)
            ->groupBy(DB::raw('YEAR(visittime)'))
            ->orderBy(DB::raw('YEAR(visittime)'))
            ->get();

        // Kirim data ke view
        return view('visitorHistory.index', [
            'results' => $results,
            'fromYear' => $fromYear,
            'toYear' => $toYear,
        ]);
    }

    public function kunjunganfak(){
        return view('pages.kunjungan.fakultas');
    }
}
