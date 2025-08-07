<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $drivers = Driver::orderBy('name')->paginate(10);
        
        return Inertia::render('Drivers/Index', [
            'drivers' => $drivers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Drivers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'id_card_number' => 'nullable|string|max:13',
        ]);

        $driver = Driver::create($validated);

        return redirect()->route('drivers.index')
            ->with('success', 'คนขับถูกสร้างเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Driver $driver)
    {
        return Inertia::render('Drivers/Show', [
            'driver' => $driver->load('deliveryNotes')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Driver $driver)
    {
        return Inertia::render('Drivers/Edit', [
            'driver' => $driver
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'id_card_number' => 'nullable|string|max:13',
        ]);

        $driver->update($validated);

        return redirect()->route('drivers.index')
            ->with('success', 'ข้อมูลคนขับถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver)
    {
        // Check if driver has delivery notes
        if ($driver->deliveryNotes()->exists()) {
            return back()->with('error', 'ไม่สามารถลบคนขับที่มีใบส่งของอยู่');
        }

        $driver->delete();

        return redirect()->route('drivers.index')
            ->with('success', 'คนขับถูกลบเรียบร้อยแล้ว');
    }
}