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
        Schema::table('ration_items', function (Blueprint $table) {
            // $table->dropForeign(['feed_id']);
            // $table->dropColumn('feed_id');
            // $table->text('feed')->after('ration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ration_items', function (Blueprint $table) {
            // $table->dropColumn('feed');
            // $table->foreignUuid('feed_id')->constrained()->onDelete('cascade')->after('ration_id');
        });
    }
};
