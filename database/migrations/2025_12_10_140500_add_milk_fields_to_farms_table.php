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
        Schema::table('farms', function (Blueprint $table) {
            $table->string('farm_type', 30)->default('standard')->after('user_id');
            // Values: 'standard', 'milk_supplier', 'milk_processor', 'both'

            $table->json('milk_pricing')->nullable()->after('farm_type');
            // Format: {"grade_a": 12000, "grade_b": 10000, "grade_c": 8000} (IDR per liter)

            $table->json('milk_supplier_info')->nullable()->after('milk_pricing');
            // Format: {active_since, supplier_code, bank_details, etc.}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropColumn(['farm_type', 'milk_pricing', 'milk_supplier_info']);
        });
    }
};
