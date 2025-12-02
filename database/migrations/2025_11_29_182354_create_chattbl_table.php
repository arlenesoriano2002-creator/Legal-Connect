<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chattbl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('sender_email')->nullable();
            $table->string('receiver_email')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('sender_role')->default('user');
            $table->timestamps();
            
            // Indexes
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('sender_email');
            $table->index('receiver_email');
            $table->index('sender_role');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chattbl');
    }
};