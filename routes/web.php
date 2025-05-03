<?php

use App\Http\Controllers\IjazahController;
use App\Http\Controllers\MouController;
use App\Http\Controllers\PelatihanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SertifikasiController;
use App\Http\Controllers\SkpController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\TranskripController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitHistory;
use App\Models\Pelatihan;
use App\Models\Staff;

Route::get('/', function () {
    return view('welcome');
});

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

Route::get('/kunjungan/prodi', [VisitHistory::class, 'kunjunganProdi'])->name('kunjungan.prodi');


require __DIR__ . '/auth.php';

// test koneksi ke database staff
// Route::get('/test-koneksi-staff', [IjazahController::class, 'testkoneksi']);