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
        Schema::create('inventory_usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_transaction_id')->constrained()->onDelete('cascade');
            $table->uuid('livestock_id')->nullable();
            $table->foreign('livestock_id')->references('id')->on('livestocks')->onDelete('cascade');
            $table->foreignId('health_record_id')->nullable()->constrained('livestock_health_records')->onDelete('cascade');
            $table->decimal('quantity_used', 15, 3);
            $table->string('usage_type'); // treatment, feed, vaccination, etc.
            $table->text('usage_notes')->nullable();
            $table->timestamp('used_at');
            $table->timestamps();
            
            $table->index(['livestock_id', 'used_at']);
            $table->index(['health_record_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_usage_records');
    }
};
