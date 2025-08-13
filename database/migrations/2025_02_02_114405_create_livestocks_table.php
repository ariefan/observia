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
        Schema::create('livestocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('farm_id');
            $table->foreignUuid('breed_id')->nullable();
            $table->foreignUuid('herd_id')->nullable()->after('breed_id');
            $table->string('aifarm_id')->unique()->nullable(); 
            $table->string('name')->nullable();
            $table->dateTime('birthdate')->nullable();
            $table->string('sex')->default('F');
            $table->string('origin')->default(1);
            $table->string('status')->default(1);
            $table->string('tag_type')->nullable();
            $table->string('tag_id');
            $table->decimal('birth_weight')->nullable();
            $table->decimal('weight')->nullable();
            $table->json('photo')->nullable();
            $table->foreignUuid('male_parent_id')->nullable()->constrained('livestocks');
            $table->foreignUuid('female_parent_id')->nullable()->constrained('livestocks');
            $table->dateTime('purchase_date')->nullable();
            $table->bigInteger('purchase_price')->nullable();
            $table->string('purchase_from')->nullable();
            $table->string('barter_livestock_id')->nullable(); 
            $table->string('barter_from')->nullable();
            $table->date('barter_date')->nullable();
            $table->string('grant_from')->nullable();
            $table->date('grant_date')->nullable();
            $table->string('borrowed_from')->nullable();
            $table->date('borrowed_date')->nullable();
            $table->date('entry_date')->nullable();
            $table->date('herd_entry_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('livestocks', function (Blueprint $table) {
            $table->dropForeign(['male_parent_id']);
            $table->dropForeign(['female_parent_id']);
        });
        Schema::dropIfExists('livestocks');
    }
};