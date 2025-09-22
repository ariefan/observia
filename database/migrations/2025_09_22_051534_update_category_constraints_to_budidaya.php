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
        // Drop existing constraints
        DB::statement('ALTER TABLE videos DROP CONSTRAINT IF EXISTS videos_category_check');
        DB::statement('ALTER TABLE articles DROP CONSTRAINT IF EXISTS articles_category_check');

        // Add new constraints with 'budidaya' instead of 'breeding'
        DB::statement("ALTER TABLE videos ADD CONSTRAINT videos_category_check CHECK (category IN ('manajemen', 'kesehatan', 'budidaya'))");
        DB::statement("ALTER TABLE articles ADD CONSTRAINT articles_category_check CHECK (category IN ('manajemen', 'kesehatan', 'budidaya'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new constraints
        DB::statement('ALTER TABLE videos DROP CONSTRAINT IF EXISTS videos_category_check');
        DB::statement('ALTER TABLE articles DROP CONSTRAINT IF EXISTS articles_category_check');

        // Restore original constraints with 'breeding'
        DB::statement("ALTER TABLE videos ADD CONSTRAINT videos_category_check CHECK (category IN ('manajemen', 'kesehatan', 'breeding'))");
        DB::statement("ALTER TABLE articles ADD CONSTRAINT articles_category_check CHECK (category IN ('manajemen', 'kesehatan', 'breeding'))");
    }
};