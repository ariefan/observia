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
        Schema::create('livestock_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('livestock_id')->constrained()->onDelete('cascade');
            $table->enum('health_status', ['healthy', 'sick']);
            $table->json('diagnosis')->nullable(); // Support multiple diagnoses
            $table->string('treatment')->nullable();
            $table->text('notes')->nullable();
            $table->json('medicines')->nullable(); // Support multiple medicines with dosage
            // Keep old columns for backward compatibility
            $table->string('medicine_name')->nullable();
            $table->string('medicine_type')->nullable();
            $table->integer('medicine_quantity')->nullable();
            $table->date('record_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestock_health_records');
    }
};
