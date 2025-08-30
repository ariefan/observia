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
        Schema::table('livestock_health_records', function (Blueprint $table) {
            $table->dropColumn([
                'medicine_name',
                'medicine_type', 
                'medicine_quantity'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestock_health_records', function (Blueprint $table) {
            $table->string('medicine_name')->nullable();
            $table->string('medicine_type')->nullable();
            $table->integer('medicine_quantity')->nullable();
        });
    }
};
