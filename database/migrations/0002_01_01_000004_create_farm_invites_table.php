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
        Schema::create('farm_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('farm_id');
            $table->string('email');
            $table->string('role')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('farm_invites')) {
            Schema::table('farm_invites', function (Blueprint $table) {
                $table->dropForeign(['farm_id']);
            });
        }
        Schema::dropIfExists('farm_invites');
    }
};
