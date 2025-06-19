<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IjazahController;
use App\Http\Controllers\MouController;
use App\Http\Controllers\PelatihanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SertifikasiController;
use App\Http\Controllers\SkpController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StatistikKoleksi;
use App\Http\Controllers\TranskripController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitHistory;
use App\Models\Pelatihan;
use App\Models\Staff;

Route::get('/', [DashboardController::class, 'totalStatistik'])->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resources([
    'ijazah' => IjazahController::class,
    'staff' => StaffController::class,
    'transkrip' => TranskripController::class,
    'sertifikasi' => SertifikasiController::class,
    'skp' => SkpController::class,
    'pelatihan' => PelatihanController::class,
    'mou' => MouController::class,
]);

Route::get('/kunjungan/prodiChart', [VisitHistory::class, 'kunjunganProdiChart'])->name('kunjungan.prodiChart');
Route::get('/kunjungan/prodiTable', [VisitHistory::class, 'kunjunganProdiTable'])->name('kunjungan.prodiTable');
Route::get('/koleksi/prosiding', [StatistikKoleksi::class, 'prosiding'])->name('koleksi.prosiding');
Route::get('/koleksi/jurnal', [StatistikKoleksi::class, 'jurnal'])->name('koleksi.jurnal');
Route::get('/koleksi/ebook', [StatistikKoleksi::class, 'ebook'])->name('koleksi.ebook');
Route::get('/koleksi/textbook', [StatistikKoleksi::class, 'textbook'])->name('koleksi.textbook');
Route::get('/koleksi/periodikal', [StatistikKoleksi::class, 'periodikal'])->name('koleksi.periodikal');
Route::get('/koleksi/referensi', [StatistikKoleksi::class, 'referensi'])->name('koleksi.referensi');
Route::get('/koleksi/prodi', [StatistikKoleksi::class, 'koleksiPerprodi'])->name('koleksi.prodi');


require __DIR__ . '/auth.php';

// test koneksi ke database staff
// Route::get('/test-koneksi-staff', [IjazahController::class, 'testkoneksi']);