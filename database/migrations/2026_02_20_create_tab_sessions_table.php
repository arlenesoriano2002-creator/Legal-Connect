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
            $table->string('tab_token', 255)->unique();
            $table->string('tab_id', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            
            // Indexes for faster lookups
            $table->index('user_id');
            $table->index('tab_token');
            $table->index('expires_at');
            
            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
