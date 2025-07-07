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
        Schema::create('livestocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('farm_id');
            $table->foreignUuid('breed_id')->nullable();
            $table->string('name');
            $table->dateTime('birthdate');
            $table->string('sex');
            $table->string('origin');
            $table->string('status');
            $table->integer('male_parent_id')->references('id')->on('livestocks')->nullable();
            $table->integer('female_parent_id')->references('id')->on('livestocks')->nullable();
            $table->dateTime('purchase_date')->nullable();
            $table->bigInteger('purchase_price')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestocks');
    }
};
