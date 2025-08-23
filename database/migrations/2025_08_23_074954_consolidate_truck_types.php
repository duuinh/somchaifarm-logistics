<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('vehicles')
            ->where('vehicle_type', '10 ล้อไม้ดั้ม')
            ->update(['vehicle_type' => '10 ล้อดั้ม']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('vehicles')
            ->where('vehicle_type', '10 ล้อดั้ม')
            ->where('license_plate', '80-2843') // Only the one that was changed
            ->update(['vehicle_type' => '10 ล้อไม้ดั้ม']);
    }
};
