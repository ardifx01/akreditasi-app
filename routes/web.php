<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IjazahController;
use App\Http\Controllers\MouController;
use App\Http\Controllers\PelatihanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SertifikasiController;
use App\Http\Controllers\SkpController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\StatistikKoleksi;
use App\Http\Controllers\TranskripController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitHistory;


Route::get('/', [DashboardController::class, 'totalStatistik'])->name('dashboard');



Route::get('/credit', function () {
    return view('credit');
})->name('credit.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resources([
        'ijazah' => IjazahController::class,
        'staff' => StaffController::class,
        'transkrip' => TranskripController::class,
        'sertifikasi' => SertifikasiController::class,
        'skp' => SkpController::class,
        'pelatihan' => PelatihanController::class,
        'mou' => MouController::class,
    ]);
});

Route::get('/kunjungan/prodiChart', [VisitHistory::class, 'kunjunganProdiChart'])->name('kunjungan.prodiChart');
Route::get('/kunjungan/prodiTable', [VisitHistory::class, 'kunjunganProdiTable'])->name('kunjungan.prodiTable');
Route::get('/kunjungan/tanggal', [VisitHistory::class, 'kunjunganTanggalTable'])->name('kunjungan.tanggalTable');


Route::get('/koleksi/prosiding', [StatistikKoleksi::class, 'prosiding'])->name('koleksi.prosiding');
Route::get('/koleksi/jurnal', [StatistikKoleksi::class, 'jurnal'])->name('koleksi.jurnal');
Route::get('/koleksi/ebook', [StatistikKoleksi::class, 'ebook'])->name('koleksi.ebook');
Route::get('/koleksi/textbook', [StatistikKoleksi::class, 'textbook'])->name('koleksi.textbook');
Route::get('/koleksi/periodikal', [StatistikKoleksi::class, 'periodikal'])->name('koleksi.periodikal');
Route::get('/koleksi/referensi', [StatistikKoleksi::class, 'referensi'])->name('koleksi.referensi');
Route::get('/koleksi/prodi', [StatistikKoleksi::class, 'koleksiPerprodi'])->name('koleksi.prodi');

Route::get('/kunjungan/cek-kehadiran', [VisitHistory::class, 'cekKehadiran'])->name('kunjungan.cekKehadiran');


Route::get('/peminjaman/peminjaman-rentang-tanggal', [PeminjamanController::class, 'pertanggal'])->name('peminjaman.peminjaman_rentang_tanggal');

Route::get('/peminjaman/peminjaman-prodi-chart', [PeminjamanController::class, 'peminjamanProdiChart'])->name('peminjaman.peminjaman_prodi_chart');
Route::get('/peminjaman/cek-histori', [PeminjamanController::class, 'checkHistory'])->name('peminjaman.check_history');

require __DIR__ . '/auth.php';
