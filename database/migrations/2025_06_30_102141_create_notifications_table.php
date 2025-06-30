<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');

            $table->string('type'); // 'invite', 'system', 'info'
            $table->string('title');
            $table->text('message');

            $table->boolean('action_required')->default(false); // TRUE = user must take action

            $table->enum('action_status', ['pending', 'accepted', 'rejected'])->nullable();
            $table->timestamp('acted_at')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
