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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->uuid('farm_id');
            $table->string('type'); // livestock-summary, feeding-report, etc.
            $table->string('name');
            $table->string('format'); // pdf, excel, csv
            $table->date('start_date');
            $table->date('end_date');
            $table->string('file_path')->nullable();
            $table->json('filters')->nullable(); // Additional filters like livestock_id
            $table->enum('status', ['generating', 'completed', 'failed'])->default('generating');
            $table->integer('file_size')->nullable(); // in bytes
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
            $table->index(['user_id', 'farm_id']);
            $table->index(['type', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
