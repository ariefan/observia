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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('farm_id');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('inventory_categories');
            $table->foreignId('unit_id')->constrained('inventory_units');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('minimum_stock', 15, 3)->default(0);
            $table->decimal('current_stock', 15, 3)->default(0);
            $table->boolean('track_expiry')->default(false);
            $table->boolean('track_batch')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('specifications')->nullable(); // For storing additional specs like concentration, etc.
            $table->timestamps();
            
            $table->unique(['farm_id', 'name', 'brand']); // Unique per farm
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
