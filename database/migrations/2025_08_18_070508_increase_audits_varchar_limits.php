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
        Schema::table('audits', function (Blueprint $table) {
            // Increase VARCHAR limits for fields that commonly exceed 255 characters
            $table->text('user_agent')->nullable()->change(); // User agents can be very long
            $table->text('url')->nullable()->change(); // URLs can exceed 255 chars
            $table->string('auditable_type', 500)->change(); // Model class names could be long
            $table->string('auditable_id', 500)->change(); // IDs could be long strings/UUIDs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            // Revert back to original VARCHAR(255) limits
            $table->string('user_agent')->nullable()->change();
            $table->string('url')->nullable()->change();
            $table->string('auditable_type')->change();
            $table->string('auditable_id')->change();
        });
    }
};
