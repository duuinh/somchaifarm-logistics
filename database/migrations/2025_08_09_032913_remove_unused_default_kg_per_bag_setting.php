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
        // Remove the unused default_kg_per_bag setting
        DB::table('settings')->where('key', 'default_kg_per_bag')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the default_kg_per_bag setting if rolled back
        DB::table('settings')->insert([
            'key' => 'default_kg_per_bag',
            'value' => '25',
            'type' => 'number',
            'description' => 'Default kg per bag conversion',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
