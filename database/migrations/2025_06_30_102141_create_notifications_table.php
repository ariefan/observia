<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // if you want UUIDs, use this
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');

            $table->string('type')->nullable(); // 'message', 'update', 'reminder', etc.
            $table->string('title');
            $table->text('message');

            $table->timestamp('read_at')->nullable(); // NULL = unread
            $table->timestamps(); // created_at = shown in UI
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
