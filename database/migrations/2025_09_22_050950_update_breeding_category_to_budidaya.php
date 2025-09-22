<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update videos table
        DB::table('videos')
            ->where('category', 'breeding')
            ->update(['category' => 'budidaya']);

        // Update articles table
        DB::table('articles')
            ->where('category', 'breeding')
            ->update(['category' => 'budidaya']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert videos table
        DB::table('videos')
            ->where('category', 'budidaya')
            ->update(['category' => 'breeding']);

        // Revert articles table
        DB::table('articles')
            ->where('category', 'budidaya')
            ->update(['category' => 'breeding']);
    }
};