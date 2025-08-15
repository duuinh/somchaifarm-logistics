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
        Schema::table('delivery_note_items', function (Blueprint $table) {
            // Add new columns
            $table->decimal('quantity', 10, 2)->after('item_id');
            $table->enum('unit_type', ['kg', 'bags'])->after('quantity');
            $table->decimal('price_per_unit', 10, 2)->after('unit_type');
            
            // Drop old columns
            $table->dropColumn([
                'quantity_kg',
                'quantity_bags',
                'unit_multiplier',
                'unit_price'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            // Restore old columns
            $table->decimal('quantity_kg', 10, 2)->nullable()->after('item_id');
            $table->decimal('quantity_bags', 10, 2)->nullable()->after('quantity_kg');
            $table->decimal('unit_multiplier', 8, 2)->default(1)->after('quantity_bags');
            $table->decimal('unit_price', 10, 2)->after('unit_multiplier');
            
            // Drop new columns
            $table->dropColumn([
                'quantity',
                'unit_type',
                'price_per_unit'
            ]);
        });
    }
};