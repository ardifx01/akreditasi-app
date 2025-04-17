<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitHistory;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/visitor-history', [VisitHistory::class, 'index'])->name('visitor.history');
Route::get('/kunjungan-fakultas', [VisitHistory::class, 'kunjunganfak'])->name('kunjungan-fakultas');
