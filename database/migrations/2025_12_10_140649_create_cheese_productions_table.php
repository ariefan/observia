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
        Schema::create('cheese_productions', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 50)->unique();
            $table->foreignUuid('farm_id')->constrained()->onDelete('cascade');

            // Production Details
            $table->string('cheese_type', 100);
            $table->date('production_date');
            $table->foreignUuid('produced_by_user_id')->constrained('users')->onDelete('restrict');

            // Milk Sources (Traceability)
            $table->json('milk_batch_ids');
            $table->decimal('total_milk_volume', 10, 2);

            // Recipe & Ingredients
            $table->text('recipe_notes')->nullable();
            $table->string('starter_culture', 100)->nullable();
            $table->string('rennet_type', 50)->nullable();
            $table->string('rennet_amount', 50)->nullable();
            $table->json('additional_ingredients')->nullable();
            $table->json('process_parameters')->nullable();

            // Output
            $table->decimal('cheese_weight_kg', 10, 2)->nullable();
            $table->decimal('yield_percentage', 5, 2)->nullable();

            // Aging
            $table->date('aging_start_date')->nullable();
            $table->integer('aging_target_days')->nullable();
            $table->timestamp('aging_completed_at')->nullable();
            $table->json('aging_notes')->nullable();

            // Status
            $table->string('status', 30)->default('in_production');

            // Inventory Link
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory_items')->onDelete('set null');

            // Storage
            $table->string('storage_location', 100)->nullable();

            // Metadata
            $table->json('process_photos')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['farm_id', 'production_date']);
            $table->index('status');
            $table->index('cheese_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cheese_productions');
    }
};
