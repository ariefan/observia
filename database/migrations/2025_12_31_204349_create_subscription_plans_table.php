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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Starter, Pro, Enterprise
            $table->string('slug')->unique(); // starter, pro, enterprise
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 12, 2)->default(0); // Price per month
            $table->decimal('annual_price', 12, 2)->default(0); // Price per year (20% discount)
            $table->json('features')->nullable(); // Array of feature objects
            $table->integer('max_livestock')->nullable(); // null = unlimited
            $table->integer('max_users')->nullable(); // null = unlimited
            $table->boolean('has_analytics')->default(false);
            $table->boolean('has_iot')->default(false);
            $table->boolean('has_expert_support')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible')->default(true); // Can be hidden by super admin
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
