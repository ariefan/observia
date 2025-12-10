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
        Schema::create('milk_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('farm_id')->constrained()->onDelete('cascade');

            // Period
            $table->date('payment_period_start');
            $table->date('payment_period_end');

            // Volume & Grading Breakdown
            $table->decimal('total_liters', 10, 2);
            $table->json('grade_breakdown');

            // Amounts
            $table->decimal('gross_amount', 15, 2);
            $table->json('deductions')->nullable();
            $table->decimal('deductions_total', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);

            // Approval Workflow
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('calculated_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('calculated_at')->nullable();
            $table->foreignUuid('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Payment Execution
            $table->foreignUuid('paid_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('payment_proof_path', 255)->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['farm_id', 'payment_period_start']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milk_payments');
    }
};
