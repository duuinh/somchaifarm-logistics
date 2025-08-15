<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Driver;
use App\Models\Item;
use App\Models\Setting;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DeliveryNote::with(['client', 'driver', 'vehicle', 'creator']);
        
        // Add search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('client', function ($clientQuery) use ($request) {
                      $clientQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        $deliveryNotes = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return Inertia::render('DeliveryNotes/Index', [
            'deliveryNotes' => $deliveryNotes,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $drivers = Driver::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('license_plate')->get();

        return Inertia::render('DeliveryNotes/Create', [
            'clients' => $clients,
            'items' => $items,
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'defaultServiceFee' => Setting::get('default_service_fee', 0),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'delivery_date' => 'required|date',
            'pricing_type' => 'required|in:regular,credit',
            'notes' => 'nullable|string',
            'service_fee' => 'nullable|numeric|min:0',
            'service_fee_per_ton' => 'nullable|numeric|min:0',
            'bag_fee' => 'nullable|numeric|min:0',
            'transport_fee' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_type' => 'required|in:kg,bags',
            'items.*.price_per_unit' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Pre-fetch all items that need kg_per_bag_conversion to avoid N+1 queries
            $itemIds = collect($validated['items'])
                ->filter(function ($item) {
                    return $item['unit_type'] === 'bags';
                })
                ->pluck('item_id')
                ->unique();
            
            $itemsMap = Item::whereIn('id', $itemIds)
                ->get()
                ->keyBy('id');

            // Calculate totals
            $totalWeight = 0;
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $totalAmount += $item['total_price'];
                
                // Calculate weight based on unit type
                if ($item['unit_type'] === 'kg') {
                    $totalWeight += $item['quantity'];
                } elseif ($item['unit_type'] === 'bags' && isset($itemsMap[$item['item_id']])) {
                    $itemModel = $itemsMap[$item['item_id']];
                    $totalWeight += $item['quantity'] * $itemModel->kg_per_bag_conversion;
                }
            }

            // Handle fees
            $serviceFee = $validated['service_fee'] ?? 0;
            $serviceFeePerTon = $validated['service_fee_per_ton'] ?? 0;
            $bagFee = $validated['bag_fee'] ?? 0;
            $transportFee = $validated['transport_fee'] ?? 0;
            $totalAmount += $serviceFee + $bagFee + $transportFee;

            // Create delivery note
            $deliveryNote = DeliveryNote::create([
                'client_id' => $validated['client_id'],
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'created_by' => Auth::id(),
                'delivery_date' => $validated['delivery_date'],
                'pricing_type' => $validated['pricing_type'],
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
                'service_fee' => $serviceFee,
                'service_fee_per_ton' => $serviceFeePerTon,
                'bag_fee' => $bagFee,
                'transport_fee' => $transportFee,
                'notes' => $validated['notes'],
            ]);

            // Create delivery note items
            foreach ($validated['items'] as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_type' => $item['unit_type'],
                    'price_per_unit' => $item['price_per_unit'],
                    'total_price' => $item['total_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('delivery-notes.index')
                ->with('success', 'ใบส่งของถูกสร้างเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาดในการสร้างใบส่งของ');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['client', 'driver', 'vehicle', 'creator', 'items.item']);

        return Inertia::render('DeliveryNotes/Show', [
            'deliveryNote' => $deliveryNote
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load('items');
        $clients = Client::orderBy('name')->get();
        $items = Item::orderBy('name')->get();
        $drivers = Driver::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('license_plate')->get();

        return Inertia::render('DeliveryNotes/Edit', [
            'deliveryNote' => $deliveryNote,
            'clients' => $clients,
            'items' => $items,
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'defaultServiceFee' => Setting::get('default_service_fee', 0),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DeliveryNote $deliveryNote)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'delivery_date' => 'required|date',
            'pricing_type' => 'required|in:regular,credit',
            'notes' => 'nullable|string',
            'service_fee' => 'nullable|numeric|min:0',
            'service_fee_per_ton' => 'nullable|numeric|min:0',
            'bag_fee' => 'nullable|numeric|min:0',
            'transport_fee' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit_type' => 'required|in:kg,bags',
            'items.*.price_per_unit' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Pre-fetch all items that need kg_per_bag_conversion to avoid N+1 queries
            $itemIds = collect($validated['items'])
                ->filter(function ($item) {
                    return $item['unit_type'] === 'bags';
                })
                ->pluck('item_id')
                ->unique();
            
            $itemsMap = Item::whereIn('id', $itemIds)
                ->get()
                ->keyBy('id');

            // Calculate totals
            $totalWeight = 0;
            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $totalAmount += $item['total_price'];
                
                // Calculate weight based on unit type
                if ($item['unit_type'] === 'kg') {
                    $totalWeight += $item['quantity'];
                } elseif ($item['unit_type'] === 'bags' && isset($itemsMap[$item['item_id']])) {
                    $itemModel = $itemsMap[$item['item_id']];
                    $totalWeight += $item['quantity'] * $itemModel->kg_per_bag_conversion;
                }
            }

            // Handle fees
            $serviceFee = $validated['service_fee'] ?? 0;
            $serviceFeePerTon = $validated['service_fee_per_ton'] ?? 0;
            $bagFee = $validated['bag_fee'] ?? 0;
            $transportFee = $validated['transport_fee'] ?? 0;
            $totalAmount += $serviceFee + $bagFee + $transportFee;

            // Update delivery note
            $deliveryNote->update([
                'client_id' => $validated['client_id'],
                'driver_id' => $validated['driver_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'delivery_date' => $validated['delivery_date'],
                'pricing_type' => $validated['pricing_type'],
                'total_weight' => $totalWeight,
                'total_amount' => $totalAmount,
                'service_fee' => $serviceFee,
                'service_fee_per_ton' => $serviceFeePerTon,
                'bag_fee' => $bagFee,
                'transport_fee' => $transportFee,
                'notes' => $validated['notes'],
            ]);

            // Delete old items and create new ones
            $deliveryNote->items()->delete();

            foreach ($validated['items'] as $item) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $deliveryNote->id,
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_type' => $item['unit_type'],
                    'price_per_unit' => $item['price_per_unit'],
                    'total_price' => $item['total_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('delivery-notes.index')
                ->with('success', 'ใบส่งของถูกอัปเดตเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'เกิดข้อผิดพลาดในการอัปเดตใบส่งของ');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeliveryNote $deliveryNote)
    {
        $deliveryNote->delete();

        return redirect()->route('delivery-notes.index')
            ->with('success', 'ใบส่งของถูกลบเรียบร้อยแล้ว');
    }

    /**
     * Display print view for delivery note
     */
    public function print(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['client', 'driver', 'vehicle', 'creator', 'items.item']);

        $companySettings = [
            'company_name' => Setting::get('company_name', ''),
            'company_address' => Setting::get('company_address', ''),
            'company_phone' => Setting::get('company_phone', ''),
            'company_tax_id' => Setting::get('company_tax_id', ''),
            'company_fax' => Setting::get('company_fax', ''),
        ];

        // Debug: Log the company settings
        \Log::info('Company Settings:', $companySettings);

        return Inertia::render('DeliveryNotes/Print', [
            'deliveryNote' => $deliveryNote,
            'companySettings' => $companySettings
        ]);
    }
}