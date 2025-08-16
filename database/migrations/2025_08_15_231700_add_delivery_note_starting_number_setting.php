<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add delivery note starting number setting
        \App\Models\Setting::set(
            'delivery_note_starting_number',
            1000,
            'number',
            'เลขที่เริ่มต้นของใบส่งของ (Delivery Note Starting Number)'
        );
        
        // Set the PostgreSQL sequence to start from the configured number (only on PostgreSQL)
        if (config('database.connections.' . config('database.default') . '.driver') === 'pgsql') {
            $startingNumber = \App\Models\Setting::get('delivery_note_starting_number', 1000);
            \Illuminate\Support\Facades\DB::statement("ALTER SEQUENCE delivery_notes_id_seq RESTART WITH {$startingNumber}");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the setting
        \App\Models\Setting::where('key', 'delivery_note_starting_number')->delete();
        
        // Reset sequence to 1 (only on PostgreSQL)
        if (config('database.connections.' . config('database.default') . '.driver') === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("ALTER SEQUENCE delivery_notes_id_seq RESTART WITH 1");
        }
    }
};
