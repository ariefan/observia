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
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('farm_id');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('alert_type'); // low_stock, expiry_warning, expired, damaged
            $table->string('severity'); // info, warning, critical
            $table->string('title');
            $table->text('message');
            $table->json('metadata')->nullable(); // Additional data like threshold values
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->uuid('resolved_by')->nullable();
            $table->foreign('resolved_by')->references('id')->on('users');
            $table->timestamps();
            
            $table->index(['farm_id', 'is_read', 'created_at']);
            $table->index(['alert_type', 'severity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_alerts');
    }
};
