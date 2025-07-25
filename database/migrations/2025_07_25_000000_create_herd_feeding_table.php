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
        Schema::create('herd_feeding', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('herd_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('ration_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 8, 2);
            $table->date('date');
            $table->time('time')->nullable();
            $table->foreignUuid('user_id');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestock_feeding');
    }
};
