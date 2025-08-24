<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Models\Client;
use App\Models\Location;
use App\Models\Item;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Dashboard', [
        'stats' => [
            'clients_count' => Client::count(),
            'items_count' => Item::count(),
            'drivers_count' => Driver::count(),
            'vehicles_count' => Vehicle::count(),
        ],
'vehicles' => Vehicle::active()
            ->whereNotNull('gps_device_id')
            ->orderBy('vehicle_type')
            ->orderBy('license_plate')
            ->get()
            ->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'gps_device_id' => $vehicle->gps_device_id,
                    'license_plate' => $vehicle->license_plate,
                    'province' => $vehicle->province,
                    'vehicle_type' => $vehicle->vehicle_type,
                    'gps_provider' => $vehicle->gps_provider,
                    'is_active' => $vehicle->is_active,
                    'color' => $vehicle->color,
                    'tracking_name' => $vehicle->tracking_name,
                    'formatted_name' => $vehicle->formatted_name,
                ];
            }),
        'locations' => Location::active()->get()
    ]);
})->middleware(['auth', 'verified'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard', [
            'stats' => [
                'clients_count' => Client::count(),
                'items_count' => Item::count(),
                'drivers_count' => Driver::count(),
                'vehicles_count' => Vehicle::count(),
            ],
            'vehicles' => Vehicle::active()
                ->whereNotNull('gps_device_id')
                ->orderBy('vehicle_type')
                ->orderBy('license_plate')
                ->get()
                ->map(function ($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'gps_device_id' => $vehicle->gps_device_id,
                        'license_plate' => $vehicle->license_plate,
                        'province' => $vehicle->province,
                        'vehicle_type' => $vehicle->vehicle_type,
                        'gps_provider' => $vehicle->gps_provider,
                        'is_active' => $vehicle->is_active,
                        'color' => $vehicle->color,
                        'tracking_name' => $vehicle->tracking_name,
                        'formatted_name' => $vehicle->formatted_name,
                    ];
                }),
            'locations' => Location::active()->get()
        ]);
    })->name('dashboard');

    // Resource routes for data management
    Route::resource('clients', ClientController::class);
    Route::resource('items', ItemController::class);
    Route::resource('drivers', DriverController::class);
    Route::resource('vehicles', VehicleController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('users', UserController::class);
        
    // Business Settings routes
    Route::get('business-settings', [SettingController::class, 'index'])->name('business-settings.index');
    Route::put('business-settings', [SettingController::class, 'update'])->name('business-settings.update');
    Route::get('api/settings/{key}', [SettingController::class, 'getValue'])->name('settings.get');
    
    // Vehicle API
    Route::prefix('api/vehicles')->group(function () {
        Route::get('with-gps', [VehicleController::class, 'getVehiclesWithGps'])->name('api.vehicles.with-gps');
    });
    
    
    // Siam GPS API Proxy
    Route::prefix('api/siamgps')->group(function () {
        Route::post('route-history', [App\Http\Controllers\Api\SiamGpsController::class, 'getRouteHistory'])->name('api.siamgps.route-history');
        Route::post('realtime', [App\Http\Controllers\Api\SiamGpsController::class, 'getRealtimeData'])->name('api.siamgps.realtime');
        Route::post('realtime/listByVehicleId/{vehicleId}', [App\Http\Controllers\Api\SiamGpsController::class, 'getRealtimeDataByVehicleId'])->name('api.siamgps.realtime.listByVehicleId');
    });
    
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
