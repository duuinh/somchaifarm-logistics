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
            // Check if column exists before dropping
            if (Schema::hasColumn('delivery_notes', 'pricing_type')) {
                // Try to drop indexes that might include pricing_type
                try {
                    $table->dropIndex('delivery_notes_created_pricing_index');
                } catch (\Exception $e) {
                    // Index might not exist, continue
                }
                
                // Drop the column
                $table->dropColumn('pricing_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            // Add the column back
            $table->enum('pricing_type', ['regular', 'credit'])->default('regular');
            // Add the index back
            $table->index(['created_at', 'pricing_type'], 'delivery_notes_created_pricing_index');
        });
    }
};
