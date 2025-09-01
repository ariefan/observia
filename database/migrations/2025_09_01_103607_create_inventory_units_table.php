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
        Schema::create('inventory_units', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('symbol', 10);
            $table->string('type'); // weight, volume, count
            $table->decimal('base_factor', 15, 6)->default(1); // conversion factor to base unit
            $table->boolean('is_base')->default(false);
            $table->timestamps();
        });

        // Insert default units
        DB::table('inventory_units')->insert([
            // Weight units
            ['name' => 'Gram', 'symbol' => 'g', 'type' => 'weight', 'base_factor' => 1, 'is_base' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'weight', 'base_factor' => 1000, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ton', 'symbol' => 't', 'type' => 'weight', 'base_factor' => 1000000, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            // Volume units
            ['name' => 'Milliliter', 'symbol' => 'ml', 'type' => 'volume', 'base_factor' => 1, 'is_base' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Liter', 'symbol' => 'l', 'type' => 'volume', 'base_factor' => 1000, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            // Count units
            ['name' => 'Buah', 'symbol' => 'pcs', 'type' => 'count', 'base_factor' => 1, 'is_base' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lusin', 'symbol' => 'lsn', 'type' => 'count', 'base_factor' => 12, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kotak', 'symbol' => 'ktk', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Botol', 'symbol' => 'btl', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Vial', 'symbol' => 'vial', 'type' => 'count', 'base_factor' => 1, 'is_base' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_units');
    }
};
