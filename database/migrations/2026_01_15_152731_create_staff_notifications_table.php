<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('staff_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('type')->default('system'); // system, appointment, message, task, etc.
            $table->string('title');
            $table->text('message');
            $table->enum('assigned_to', ['individual', 'all'])->default('individual');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['staff_id', 'is_read', 'created_at']);
            $table->index(['assigned_to', 'is_read']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_notifications');
    }
}