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
        Schema::table('livestocks', function (Blueprint $table) {
            $table->string('aifarm_id')->unique()->nullable()->after('id');
            $table->string('tag_type')->nullable()->after('status');
            $table->string('tag_id')->nullable()->after('tag_type');
            $table->decimal('birth_weight')->nullable()->after('tag_id');
            $table->decimal('weight')->nullable()->after('birth_weight');
            $table->json('photo')->nullable()->after('weight');
            $table->string('barter_livestock_id')->nullable();
            $table->string('barter_from')->nullable();
            $table->date('barter_date')->nullable();

            // Drop old parent columns if they exist with wrong type
            if (Schema::hasColumn('livestocks', 'male_parent_id')) {
                $table->dropColumn('male_parent_id');
            }
            if (Schema::hasColumn('livestocks', 'female_parent_id')) {
                $table->dropColumn('female_parent_id');
            }
        });

        Schema::table('livestocks', function (Blueprint $table) {
            $table->foreignUuid('male_parent_id')->nullable()->references('id')->on('livestocks')->after('breed_id');
            $table->foreignUuid('female_parent_id')->nullable()->references('id')->on('livestocks')->after('male_parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestocks', function (Blueprint $table) {
            $table->dropColumn('aifarm_id');
            $table->dropColumn('tag_type');
            $table->dropColumn('tag_id');
            $table->dropColumn('birth_weight');
            $table->dropColumn('weight');
            $table->dropColumn('photo');
            $table->dropColumn('barter_livestock_id');
            $table->dropColumn('barter_from');
            $table->dropColumn('barter_date');
            $table->dropForeign(['male_parent_id']);
            $table->dropForeign(['female_parent_id']);
            $table->dropColumn('male_parent_id');
            $table->dropColumn('female_parent_id');
        });
    }
};
