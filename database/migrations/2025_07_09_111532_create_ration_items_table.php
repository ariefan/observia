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
        Schema::create('ration_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('ration_id')->constrained()->onDelete('cascade');
            $table->text('feed')->after('ration_id');
            $table->decimal('quantity', 8, 2);
            $table->decimal('price', 15, 2);
            $table->foreignUuid('farm_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ration_items');
    }
};
