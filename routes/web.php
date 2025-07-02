<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuruScanController;
use App\Http\Controllers\SiswaScanController;
 use Barryvdh\DomPDF\Facade\Pdf;
 use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import Facade QrCode
 
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    //After Login the routes are accept by the loginUsers...
    Route::post('/guru-scan/store', [GuruScanController::class, 'store']);
    Route::post('/siswa-scan/store', [SiswaScanController::class, 'store']);
    Route::get('/siswa-scan', [SiswaScanController::class, 'index']);
    Route::get('/pengaturan', function() {
        $pengaturan = \App\Models\Pengaturan::select('latitude', 'longitude', 'radius')->first();
        $posisi = [$pengaturan->latitude, $pengaturan->longitude, $pengaturan->radius];
        return $posisi;
    });
});

