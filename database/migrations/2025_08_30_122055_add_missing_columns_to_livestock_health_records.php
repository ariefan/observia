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
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('livestock_health_records', 'diagnosis')) {
                $table->json('diagnosis')->nullable();
            }
            if (!Schema::hasColumn('livestock_health_records', 'medicines')) {
                $table->json('medicines')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestock_health_records', function (Blueprint $table) {
            $table->dropColumn(['diagnosis', 'medicines']);
        });
    }
};
