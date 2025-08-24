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
        // Vehicles table indexes  
        Schema::table('vehicles', function (Blueprint $table) {
            $table->index('license_plate'); // Searched by license plate, ordered by
            $table->index('vehicle_type'); // Searched by vehicle type
            $table->index('province'); // Searched by province
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('name'); // Searched by name, ordered by
            $table->index('email'); // Searched by email, unique constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['name']);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['province']);
            $table->dropIndex(['vehicle_type']);
            $table->dropIndex(['license_plate']);
        });
    }
};
