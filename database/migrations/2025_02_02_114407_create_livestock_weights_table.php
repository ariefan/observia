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
        Schema::create('livestock_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('livestock_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->date('date');
            $table->string('device_id')->nullable();
            $table->foreignUuid('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('livestock_weights')) {
            Schema::table('livestock_weights', function (Blueprint $table) {
                $table->dropForeign(['livestock_id']);
                $table->dropForeign(['user_id']);
            });
        }
        Schema::dropIfExists('livestock_weights');
    }
};