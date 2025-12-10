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
        Schema::create('milk_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code', 50)->unique();
            $table->foreignUuid('farm_id')->constrained()->onDelete('cascade');
            $table->date('collection_date');
            $table->string('session', 20)->nullable();
            $table->decimal('total_volume', 10, 2);
            $table->json('source_livestock_milking_ids')->nullable();

            // Collection & Transport
            $table->foreignUuid('collected_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('collected_at')->nullable();
            $table->decimal('estimated_volume', 10, 2)->nullable();
            $table->decimal('actual_volume', 10, 2)->nullable();
            $table->decimal('variance_percentage', 5, 2)->nullable();
            $table->decimal('transport_temp_pickup', 4, 1)->nullable();
            $table->decimal('transport_temp_delivery', 4, 1)->nullable();
            $table->integer('transport_duration_minutes')->nullable();
            $table->text('transport_notes')->nullable();

            // Receiving
            $table->foreignUuid('received_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('received_at')->nullable();
            $table->string('visual_check', 20)->nullable();
            $table->string('smell_check', 20)->nullable();

            // Quality Control
            $table->foreignUuid('quality_tested_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('quality_tested_at')->nullable();
            $table->json('quality_data')->nullable();
            $table->string('quality_grade', 10)->nullable();
            $table->text('quality_notes')->nullable();

            // Status
            $table->string('status', 30)->default('collected');
            $table->text('rejection_reason')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['farm_id', 'collection_date']);
            $table->index('status');
            $table->index('batch_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milk_batches');
    }
};
