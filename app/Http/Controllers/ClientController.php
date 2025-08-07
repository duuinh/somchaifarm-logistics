<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::orderBy('name')->paginate(10);
        
        return Inertia::render('Clients/Index', [
            'clients' => $clients
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'customer_type' => 'required|in:regular,credit,special',
        ]);

        $client = Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'ลูกค้าถูกสร้างเรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        return Inertia::render('Clients/Show', [
            'client' => $client->load('deliveryNotes')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'customer_type' => 'required|in:regular,credit,special',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'ข้อมูลลูกค้าถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        // Check if client has delivery notes
        if ($client->deliveryNotes()->exists()) {
            return back()->with('error', 'ไม่สามารถลบลูกค้าที่มีใบส่งของอยู่');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'ลูกค้าถูกลบเรียบร้อยแล้ว');
    }
}