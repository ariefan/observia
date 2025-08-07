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
        Schema::create('feeding_leftovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feeding_id')->constrained('herd_feedings')->onDelete('cascade');
            $table->decimal('leftover_quantity', 8, 2);
            $table->date('date');
            $table->time('time')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('user_id');
            $table->timestamps();

            // Ensure one leftover record per feeding
            $table->unique('feeding_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feeding_leftovers');
    }
};
