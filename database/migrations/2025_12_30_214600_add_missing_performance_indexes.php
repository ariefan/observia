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
        // Indexes for livestock_milkings table
        Schema::table('livestock_milkings', function (Blueprint $table) {
            $table->index('livestock_id', 'idx_milkings_livestock');
            $table->index('date', 'idx_milkings_date');
            $table->index('user_id', 'idx_milkings_user');
            $table->index(['livestock_id', 'date'], 'idx_milkings_livestock_date');
        });

        // Indexes for livestock_weights table
        Schema::table('livestock_weights', function (Blueprint $table) {
            $table->index('livestock_id', 'idx_weights_livestock');
            $table->index('date', 'idx_weights_date');
            $table->index(['livestock_id', 'date'], 'idx_weights_livestock_date');
        });

        // Indexes for livestock_endings table
        Schema::table('livestock_endings', function (Blueprint $table) {
            $table->index('farm_id', 'idx_endings_farm');
            $table->index('livestock_id', 'idx_endings_livestock');
            $table->index('ending_status', 'idx_endings_status');
        });

        // Indexes for milk_batches table
        Schema::table('milk_batches', function (Blueprint $table) {
            $table->index('farm_id', 'idx_milk_batches_farm');
            $table->index('status', 'idx_milk_batches_status');
            $table->index('collection_date', 'idx_milk_batches_date');
            $table->index(['farm_id', 'status'], 'idx_milk_batches_farm_status');
        });

        // Indexes for milk_payments table
        Schema::table('milk_payments', function (Blueprint $table) {
            $table->index('farm_id', 'idx_milk_payments_farm');
            $table->index('status', 'idx_milk_payments_status');
            $table->index(['status', 'paid_at'], 'idx_milk_payments_status_paid');
        });

        // Indexes for inventory_batches table
        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->index('expiry_date', 'idx_inv_batches_expiry');
            $table->index(['is_active', 'current_quantity'], 'idx_inv_batches_active_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestock_milkings', function (Blueprint $table) {
            $table->dropIndex('idx_milkings_livestock');
            $table->dropIndex('idx_milkings_date');
            $table->dropIndex('idx_milkings_user');
            $table->dropIndex('idx_milkings_livestock_date');
        });

        Schema::table('livestock_weights', function (Blueprint $table) {
            $table->dropIndex('idx_weights_livestock');
            $table->dropIndex('idx_weights_date');
            $table->dropIndex('idx_weights_livestock_date');
        });

        Schema::table('livestock_endings', function (Blueprint $table) {
            $table->dropIndex('idx_endings_farm');
            $table->dropIndex('idx_endings_livestock');
            $table->dropIndex('idx_endings_status');
        });

        Schema::table('milk_batches', function (Blueprint $table) {
            $table->dropIndex('idx_milk_batches_farm');
            $table->dropIndex('idx_milk_batches_status');
            $table->dropIndex('idx_milk_batches_date');
            $table->dropIndex('idx_milk_batches_farm_status');
        });

        Schema::table('milk_payments', function (Blueprint $table) {
            $table->dropIndex('idx_milk_payments_farm');
            $table->dropIndex('idx_milk_payments_status');
            $table->dropIndex('idx_milk_payments_status_paid');
        });

        Schema::table('inventory_batches', function (Blueprint $table) {
            $table->dropIndex('idx_inv_batches_expiry');
            $table->dropIndex('idx_inv_batches_active_qty');
        });
    }
};
