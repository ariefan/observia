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
        Schema::create('livestock_milkings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('livestock_id')->constrained()->onDelete('cascade');
            $table->decimal('milk_volume', 8, 2);
            $table->date('date');
            $table->time('time')->nullable();
            $table->string('session')->nullable();
            $table->string('device_id')->nullable();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestock_milkings', function (Blueprint $table) {
            $table->dropForeign(['livestock_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('livestock_milkings');
    }
};