<?php

use App\Http\Controllers\IjazahController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitHistory;
use App\Models\Staff;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::resources([
    'ijazah' => IjazahController::class,
    'staff' => StaffController::class
]);

// test koneksi ke database staff
// Route::get('/test-koneksi-staff', [IjazahController::class, 'testkoneksi']);