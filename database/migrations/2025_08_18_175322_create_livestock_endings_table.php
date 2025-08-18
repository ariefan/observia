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
        Schema::create('livestock_endings', function (Blueprint $table) {
            $table->id();
            $table->uuid('livestock_id');
            $table->uuid('farm_id');
            $table->date('ending_date');
            $table->enum('ending_status', ['sold', 'gifted', 'loaned', 'died', 'slaughtered']);
            
            // Fields for sold/gifted
            $table->string('buyer_name')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_email')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            
            // Fields for loaned
            $table->string('receiving_farm_name')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->string('receiver_email')->nullable();
            $table->date('loan_date')->nullable();
            $table->date('return_date')->nullable();
            
            $table->text('notes')->nullable();
            $table->uuid('recorded_by'); // user_id who recorded this
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('livestock_id')->references('id')->on('livestocks')->onDelete('cascade');
            $table->foreign('farm_id')->references('id')->on('farms')->onDelete('cascade');
            $table->foreign('recorded_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestock_endings');
    }
};
