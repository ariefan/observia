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
        Schema::create('herds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('farm_id');
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->integer('capacity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('herds');
    }
};
