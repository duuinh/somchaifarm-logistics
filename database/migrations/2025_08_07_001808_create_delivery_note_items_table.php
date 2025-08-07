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
        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->enum('item_type', ['material', 'service'])->default('material');
            $table->decimal('quantity_kg', 8, 2)->nullable();
            $table->decimal('quantity_bags', 8, 2)->nullable();
            $table->decimal('quantity_tons', 8, 2)->nullable();
            $table->decimal('quantity_units', 8, 2)->nullable();
            $table->decimal('unit_multiplier', 8, 2)->default(1.00);
            $table->decimal('unit_price', 8, 2)->nullable();
            $table->decimal('total_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
