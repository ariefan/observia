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
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 7)->default('#6b7280');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default categories
        DB::table('inventory_categories')->insert([
            ['name' => 'Obat-obatan', 'description' => 'Obat-obatan dan perawatan veteriner', 'icon' => 'pill', 'color' => '#ef4444', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pakan', 'description' => 'Pakan ternak dan suplemen', 'icon' => 'wheat', 'color' => '#f59e0b', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Peralatan', 'description' => 'Alat dan peralatan peternakan', 'icon' => 'wrench', 'color' => '#3b82f6', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Perlengkapan', 'description' => 'Perlengkapan umum peternakan', 'icon' => 'package', 'color' => '#10b981', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_categories');
    }
};
