<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Resource routes for data management
    Route::resource('clients', ClientController::class);
    Route::resource('items', ItemController::class);
    Route::resource('drivers', DriverController::class);
    Route::resource('vehicles', VehicleController::class);
    
    // Delivery Notes routes
    Route::resource('delivery-notes', DeliveryNoteController::class);
    Route::get('delivery-notes/{delivery_note}/print', [DeliveryNoteController::class, 'print'])
        ->name('delivery-notes.print');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
