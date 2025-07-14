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
        Schema::table('livestocks', function (Blueprint $table) {
            // $table->foreignUuid('herd_id')->nullable()->after('breed_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestocks', function (Blueprint $table) {
            // $table->dropColumn('herd_id');
        });
    }
};
