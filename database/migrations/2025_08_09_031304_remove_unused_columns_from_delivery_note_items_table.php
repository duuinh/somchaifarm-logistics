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
            $table->dropColumn(['quantity_tons', 'quantity_units']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $table->decimal('quantity_tons', 8, 2)->nullable()->after('quantity_bags');
            $table->decimal('quantity_units', 8, 2)->nullable()->after('quantity_tons');
        });
    }
};
