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
            $table->decimal('service_fee_per_ton', 8, 2)->default(300.00)->after('total_amount');
            $table->decimal('service_fee', 10, 2)->nullable()->after('service_fee_per_ton');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropColumn(['service_fee_per_ton', 'service_fee']);
        });
    }
};
