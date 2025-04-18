<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\WaBlastController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    // Device routes
    Route::get('devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('devices/create', [DeviceController::class, 'create'])->name('devices.create');
    Route::post('devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::get('devices/{id}', [DeviceController::class, 'show'])->name('devices.show');
    Route::get('devices/{id}/edit', [DeviceController::class, 'edit'])->name('devices.edit');
    Route::put('devices/{id}', [DeviceController::class, 'update'])->name('devices.update');
    Route::get('devices/qr-code/{id}', [DeviceController::class, 'qrCode'])->name('devices.qr-code');

    // Integrasi WAblast Routes
    Route::get('/generate-qr', [WaBlastController::class, 'generateQR'])->name('blast.generate.qr');
});
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
