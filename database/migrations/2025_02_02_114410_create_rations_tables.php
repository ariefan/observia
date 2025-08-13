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
        Schema::create('rations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('history_rations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action')->default('feed');
            $table->foreignUuid('ration_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('farm_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_rations', function (Blueprint $table) {
            $table->dropForeign(['ration_id']);
            $table->dropForeign(['farm_id']);
        });
        Schema::dropIfExists('history_rations');
        
        Schema::table('rations', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
        });
        Schema::dropIfExists('rations');
    }
};