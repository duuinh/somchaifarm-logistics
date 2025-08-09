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
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'regular_price_per_ton',
                'regular_price_per_unit', 
                'credit_price_per_ton',
                'credit_price_per_unit'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('regular_price_per_ton', 8, 2)->nullable()->after('regular_price_per_bag');
            $table->decimal('regular_price_per_unit', 8, 2)->nullable()->after('regular_price_per_ton');
            $table->decimal('credit_price_per_ton', 8, 2)->nullable()->after('credit_price_per_bag');
            $table->decimal('credit_price_per_unit', 8, 2)->nullable()->after('credit_price_per_ton');
        });
    }
};
