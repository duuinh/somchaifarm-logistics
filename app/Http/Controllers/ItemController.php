<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::orderBy('name')->paginate(10);
        
        return Inertia::render('Items/Index', [
            'items' => $items
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Items/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_type' => 'required|in:kg,bags,ton,unit,both',
            'regular_price_per_kg' => 'nullable|numeric|min:0',
            'regular_price_per_bag' => 'nullable|numeric|min:0',
            'regular_price_per_ton' => 'nullable|numeric|min:0',
            'regular_price_per_unit' => 'nullable|numeric|min:0',
            'credit_price_per_kg' => 'nullable|numeric|min:0',
            'credit_price_per_bag' => 'nullable|numeric|min:0',
            'credit_price_per_ton' => 'nullable|numeric|min:0',
            'credit_price_per_unit' => 'nullable|numeric|min:0',
            'kg_per_bag_conversion' => 'required|numeric|min:0',
        ]);

        $item = Item::create($validated);

        return redirect()->route('items.index')
            ->with('success', 'รายการสินค้าถูกสร้างเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return Inertia::render('Items/Show', [
            'item' => $item->load('deliveryNoteItems')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        return Inertia::render('Items/Edit', [
            'item' => $item
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit_type' => 'required|in:kg,bags,ton,unit,both',
            'regular_price_per_kg' => 'nullable|numeric|min:0',
            'regular_price_per_bag' => 'nullable|numeric|min:0',
            'regular_price_per_ton' => 'nullable|numeric|min:0',
            'regular_price_per_unit' => 'nullable|numeric|min:0',
            'credit_price_per_kg' => 'nullable|numeric|min:0',
            'credit_price_per_bag' => 'nullable|numeric|min:0',
            'credit_price_per_ton' => 'nullable|numeric|min:0',
            'credit_price_per_unit' => 'nullable|numeric|min:0',
            'kg_per_bag_conversion' => 'required|numeric|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', 'ข้อมูลสินค้าถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        // Check if item is used in delivery notes
        if ($item->deliveryNoteItems()->exists()) {
            return back()->with('error', 'ไม่สามารถลบสินค้าที่ถูกใช้ในใบส่งของ');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'สินค้าถูกลบเรียบร้อยแล้ว');
    }
}