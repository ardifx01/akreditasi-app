<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IjazahController;
use App\Http\Controllers\Api\MouController;
use App\Http\Controllers\Api\PelatihanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SertifikasiController;
use App\Http\Controllers\Api\SkpController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\StatistikController;
use App\Http\Controllers\Api\StatistikKoleksiController;
use App\Http\Controllers\Api\TranskripController;
use App\Http\Controllers\Api\PeminjamanController;
use App\Http\Controllers\Api\VisitHistoryController;

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/credit', [DashboardController::class, 'credit']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);

    Route::apiResources([
        'ijazah' => IjazahController::class,
        'staff' => StaffController::class,
        'transkrip' => TranskripController::class,
        'sertifikasi' => SertifikasiController::class,
        'skp' => SkpController::class,
        'pelatihan' => PelatihanController::class,
        'mou' => MouController::class,
    ]);
});

Route::get('/kunjungan/prodi-chart', [VisitHistoryController::class, 'prodiChart']);
Route::get('/kunjungan/prodi-table', [VisitHistoryController::class, 'prodiTable']);
Route::get('/kunjungan/tanggal', [VisitHistoryController::class, 'tanggalTable']);

Route::get('/koleksi/prosiding', [StatistikKoleksiController::class, 'prosiding']);
Route::get('/koleksi/jurnal', [StatistikKoleksiController::class, 'jurnal']);
Route::get('/koleksi/ebook', [StatistikKoleksiController::class, 'ebook']);
Route::get('/koleksi/textbook', [StatistikKoleksiController::class, 'textbook']);
Route::get('/koleksi/periodikal', [StatistikKoleksiController::class, 'periodikal']);
Route::get('/koleksi/referensi', [StatistikKoleksiController::class, 'referensi']);
Route::get('/koleksi/prodi', [StatistikKoleksiController::class, 'koleksiPerprodi']);

Route::get('/kunjungan/cek-kehadiran', [VisitHistoryController::class, 'cekKehadiran']);

Route::get('/peminjaman/rentang-tanggal', [PeminjamanController::class, 'pertanggal']);
Route::get('/peminjaman/prodi-chart', [PeminjamanController::class, 'prodiChart']);
Route::get('/peminjaman/cek-histori', [PeminjamanController::class, 'checkHistory']);
Route::get('/peminjaman/berlangsung', [PeminjamanController::class, 'berlangsung']);
Route::get('/peminjaman/export-berlangsung', [PeminjamanController::class, 'exportBerlangsung']);
Route::get('/peminjaman/detail', [PeminjamanController::class, 'detail']);
Route::get('/kunjungan/export-kehadiran', [VisitHistoryController::class, 'exportKehadiran']);
Route::get('/kunjungan/export-harian', [VisitHistoryController::class, 'exportHarian']);
Route::get('/kunjungan/export-prodi', [VisitHistoryController::class, 'exportProdi']);
Route::get('/peminjaman/export-borrowing', [PeminjamanController::class, 'exportBorrowing']);
Route::get('/peminjaman/export-return', [PeminjamanController::class, 'exportReturn']);
Route::get('/peminjaman/peminjam-detail', [PeminjamanController::class, 'peminjamDetail']);
Route::get('/koleksi/detail', [StatistikKoleksiController::class, 'detail']);
Route::get('/kunjungan/prodi-table/detail-pengunjung', [VisitHistoryController::class, 'detailPengunjung']);
Route::get('/kunjungan/detail-pengunjung-harian', [VisitHistoryController::class, 'detailPengunjungHarian']);
