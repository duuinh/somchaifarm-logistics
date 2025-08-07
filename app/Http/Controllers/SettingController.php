<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = Setting::orderBy('key')->get();
        
        return Inertia::render('BusinessSettings/Index', [
            'settings' => $settings
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'required|in:string,number,boolean',
        ]);

        foreach ($validated['settings'] as $settingData) {
            Setting::set(
                $settingData['key'],
                $settingData['value'],
                $settingData['type']
            );
        }

        return back()->with('success', 'การตั้งค่าถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Get setting value by key (API endpoint)
     */
    public function getValue(string $key)
    {
        $value = Setting::get($key);
        
        return response()->json([
            'key' => $key,
            'value' => $value,
        ]);
    }
}
