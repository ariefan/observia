<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add medicine units to inventory_units table
        DB::table('inventory_units')->insert([
            ['name' => 'Tablet', 'symbol' => 'tablet', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kapsul', 'symbol' => 'kapsul', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ampul', 'symbol' => 'ampul', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tube', 'symbol' => 'tube', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sachet', 'symbol' => 'sachet', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('inventory_units')->whereIn('symbol', ['tablet', 'kapsul', 'ampul', 'tube', 'sachet'])->delete();
    }
};