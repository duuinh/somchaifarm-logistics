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
        Schema::table('delivery_notes', function (Blueprint $table) {
            // Add index for delivery_date (frequently queried for filtering and ordering)
            $table->index('delivery_date');
            
            // Add index for created_at (used for ordering in lists and dashboard)
            $table->index('created_at');
            
            // Add index for pricing_type (potential filter column)
            $table->index('pricing_type');
            
            // Add composite index for common query patterns
            $table->index(['created_at', 'pricing_type'], 'delivery_notes_created_pricing_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            // Drop the indexes in reverse order
            $table->dropIndex('delivery_notes_created_pricing_index');
            $table->dropIndex(['pricing_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['delivery_date']);
        });
    }
};
