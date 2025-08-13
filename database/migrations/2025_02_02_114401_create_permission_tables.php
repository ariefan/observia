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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignUuid('farm_id')->nullable()->constrained()->onDelete('cascade'); // null = global
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
        });

        // Schema::create('user_role', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
        //     $table->foreignUuid('farm_id')->nullable()->constrained()->onDelete('cascade'); // null = global
        //     $table->foreignId('role_id')->constrained()->onDelete('cascade');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('user_role', function (Blueprint $table) {
        //     $table->dropForeign(['user_id']);
        //     $table->dropForeign(['farm_id']);
        //     $table->dropForeign(['role_id']);
        // });
        // Schema::dropIfExists('user_role');
        
        Schema::table('role_permission', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['permission_id']);
        });
        Schema::dropIfExists('role_permission');
        
        Schema::dropIfExists('permissions');
        
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['farm_id']);
        });
        Schema::dropIfExists('roles');
    }
};