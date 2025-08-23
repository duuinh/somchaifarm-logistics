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
        Schema::table('vehicles', function (Blueprint $table) {
            // GPS tracking fields
            $table->unsignedBigInteger('gps_device_id')->nullable()->after('load_capacity');
            $table->string('gps_provider', 50)->nullable()->after('gps_device_id'); // 'andaman', 'siamgps', etc.
            $table->string('category', 50)->nullable()->after('gps_provider'); // 'pickup', 'trailer', 'truck'
            $table->string('shortname', 50)->nullable()->after('category');
            $table->string('emoji', 10)->nullable()->after('shortname');
            $table->boolean('is_active')->default(true)->after('emoji');
            
            // Index for performance
            $table->index('gps_device_id');
            $table->index('gps_provider');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['gps_device_id']);
            $table->dropIndex(['gps_provider']);
            $table->dropIndex(['is_active']);
            
            $table->dropColumn([
                'gps_device_id',
                'gps_provider',
                'category',
                'shortname',
                'emoji',
                'is_active'
            ]);
        });
    }
};
