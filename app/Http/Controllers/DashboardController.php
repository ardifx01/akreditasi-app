<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function totalStatistik()
    {
        $totalJurnal = 100;
        $totalBuku = 100;
        $totalEksemplar = 100;
        $anggotaAktif = 100;
        $totalKunjungan = 100;
        return view('welcome', compact('totalJurnal', 'totalBuku', 'totalEksemplar', 'anggotaAktif', 'totalKunjungan'));
    }
}
