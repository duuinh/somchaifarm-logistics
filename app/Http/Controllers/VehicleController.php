<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Vehicle::query();
        
        // Add search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('license_plate', 'like', '%' . $request->search . '%')
                  ->orWhere('vehicle_type', 'like', '%' . $request->search . '%')
                  ->orWhere('province', 'like', '%' . $request->search . '%');
            });
        }
        
        $vehicles = $query->orderBy('license_plate')->paginate(10);
        
        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Vehicles/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'license_plate' => 'required|string|max:20',
            'province' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
            'load_capacity' => 'nullable|numeric|min:0',
            'gps_device_id' => 'nullable|integer',
            'gps_provider' => 'nullable|string|in:andaman,siamgps',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
        ]);

        $vehicle = Vehicle::create($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'รถถูกสร้างเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        return Inertia::render('Vehicles/Show', [
            'vehicle' => $vehicle->load('deliveryNotes')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        return Inertia::render('Vehicles/Edit', [
            'vehicle' => $vehicle
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'license_plate' => 'required|string|max:20',
            'province' => 'nullable|string|max:100',
            'vehicle_type' => 'nullable|string|max:100',
            'load_capacity' => 'nullable|numeric|min:0',
            'gps_device_id' => 'nullable|integer',
            'gps_provider' => 'nullable|string|in:andaman,siamgps',
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
        ]);

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'ข้อมูลรถถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        // Check if vehicle has delivery notes
        if ($vehicle->deliveryNotes()->exists()) {
            return back()->with('error', 'ไม่สามารถลบรถที่มีใบส่งของอยู่');
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'รถถูกลบเรียบร้อยแล้ว');
    }
    
    /**
     * Get vehicles with GPS tracking for API
     */
    public function getVehiclesWithGps(Request $request)
    {
        $vehicles = Vehicle::active()
            ->whereNotNull('gps_device_id')
            ->orderBy('vehicle_type')
            ->orderBy('license_plate')
            ->get();
        
        // Format for tracking system
        $formattedVehicles = $vehicles->map(function ($vehicle) {
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
        });
        
        return response()->json($formattedVehicles);
    }
}