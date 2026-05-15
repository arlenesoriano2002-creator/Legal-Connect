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
        Schema::create('tab_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('tab_token', 255)->unique()->index(); // Unique token per tab
            $table->string('tab_id', 255)->unique(); // Client-generated UUID for the tab
            $table->timestamp('expires_at')->nullable(); // When the token expires
            $table->timestamps();
            
            // Foreign key to users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Index for cleanup queries
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tab_sessions');
    }
};
