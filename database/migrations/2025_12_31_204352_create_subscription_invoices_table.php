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
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->foreignUuid('farm_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('subscription_id')->constrained('farm_subscriptions')->onDelete('cascade');
            $table->foreignUuid('plan_id')->constrained('subscription_plans')->onDelete('restrict');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded', 'overdue'])->default('pending');
            $table->timestamp('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // bank_transfer, e-wallet, etc.
            $table->string('payment_reference')->nullable();
            $table->string('payment_proof')->nullable(); // File path
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Extra data
            $table->foreignUuid('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['farm_id', 'status']);
            $table->index('due_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};
