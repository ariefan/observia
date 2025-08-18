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
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level')->index(); // error, warning, critical, etc.
            $table->text('message'); // Error message as it is
            $table->json('context')->nullable(); // Additional context data
            $table->string('file')->nullable(); // File where error occurred
            $table->integer('line')->nullable(); // Line number where error occurred
            $table->text('stack_trace')->nullable(); // Stack trace
            $table->string('url')->nullable(); // URL where error occurred
            $table->string('ip_address')->nullable(); // IP address of user
            $table->string('user_agent')->nullable(); // User agent
            $table->uuid('user_id')->nullable(); // User who triggered the error
            $table->uuid('farm_id')->nullable(); // Farm context
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('set null');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
