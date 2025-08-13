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
        Schema::create('herd_feedings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('herd_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('ration_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 8, 2);
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
        Schema::table('herd_feedings', function (Blueprint $table) {
            $table->dropForeign(['herd_id']);
            $table->dropForeign(['ration_id']);
            $table->dropForeign(['user_id']);
        });
        Schema::dropIfExists('herd_feedings');
    }
};