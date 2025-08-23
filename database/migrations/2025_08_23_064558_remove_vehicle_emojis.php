<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First make emoji column nullable
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('emoji', 10)->nullable()->change();
        });
        
        // Then set all emoji fields to null to remove vehicle emojis
        DB::table('vehicles')->update(['emoji' => null]);
    }

    public function down(): void
    {
        // No rollback needed as we're just clearing emoji data
    }
};