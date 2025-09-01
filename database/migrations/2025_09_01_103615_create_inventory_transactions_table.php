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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained()->onDelete('cascade');
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Who made the transaction
            $table->string('type'); // in, out, adjustment, expired, damaged
            $table->string('reference_type')->nullable(); // purchase, sale, usage, etc.
            $table->string('reference_id')->nullable(); // Reference to related record
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Store additional data like livestock_ids for usage
            $table->timestamp('transaction_date');
            $table->timestamps();
            
            $table->index(['inventory_item_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
