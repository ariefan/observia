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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            
            // User who performed the action
            $table->uuid('user_id')->nullable();
            $table->string('user_name')->nullable(); // Store name for historical record
            $table->string('user_email')->nullable(); // Store email for historical record
            
            // Model information
            $table->string('auditable_type'); // Model class name
            $table->string('auditable_id'); // Model ID
            
            // Action performed
            $table->string('event'); // created, updated, deleted, restored
            
            // Data changes
            $table->json('old_values')->nullable(); // Previous values
            $table->json('new_values')->nullable(); // New values
            
            // Request information
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            
            // Farm context for multi-tenancy
            $table->uuid('farm_id')->nullable();
            
            // Additional metadata
            $table->json('metadata')->nullable(); // Additional context data
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id']);
            $table->index(['farm_id']);
            $table->index(['event']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
