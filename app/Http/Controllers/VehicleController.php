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
    public function index()
    {
        $vehicles = Vehicle::orderBy('license_plate')->paginate(10);
        
        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles
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
}