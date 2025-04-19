<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\WaBlastController;
use App\Http\Controllers\SubscriptionController;

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

    // Subscription routes
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/create', [SubscriptionController::class, 'create'])->name('subscriptions.create');
    Route::post('subscriptions', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('subscriptions/plan', [SubscriptionController::class, 'planList'])->name('subscriptions.plan');
    Route::get('subscriptions/checkout/{id}', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::post('subscriptions/payment', [SubscriptionController::class, 'payment'])->name('subscriptions.payment');
    Route::get('subscriptions/invoice', [SubscriptionController::class, 'invoice'])->name('subscriptions.invoice');
    Route::post('subscriptions/invoice', [SubscriptionController::class, 'updateStatusInvoice'])->name('subscriptions.invoice.status');
    Route::get('subscriptions/invoice/{id}', [SubscriptionController::class, 'showInvoice'])->name('subscriptions.invoice.show');

    // Contact routes
    Route::get('contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('contacts', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('contacts/{id}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('contacts/{id}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('contacts/{id}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    // groups
    Route::get('groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('groups/{id}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('groups/{id}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');

    // Integrasi WAblast Routes
    Route::get('/generate-qr', [WaBlastController::class, 'generateQR'])->name('blast.generate.qr');
    Route::get('/device-check', [WaBlastController::class, 'checkDeviceStatus'])->name('blast.device.check');
});
require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
