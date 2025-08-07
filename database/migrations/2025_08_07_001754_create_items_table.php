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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('item_type', ['material', 'service'])->default('material');
            $table->enum('unit_type', ['kg', 'bags', 'ton', 'unit', 'both'])->default('both');
            $table->decimal('regular_price_per_kg', 8, 2)->nullable();
            $table->decimal('regular_price_per_bag', 8, 2)->nullable();
            $table->decimal('regular_price_per_ton', 8, 2)->nullable();
            $table->decimal('regular_price_per_unit', 8, 2)->nullable();
            $table->decimal('credit_price_per_kg', 8, 2)->nullable();
            $table->decimal('credit_price_per_bag', 8, 2)->nullable();
            $table->decimal('credit_price_per_ton', 8, 2)->nullable();
            $table->decimal('credit_price_per_unit', 8, 2)->nullable();
            $table->decimal('kg_per_bag_conversion', 8, 2)->default(25.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
