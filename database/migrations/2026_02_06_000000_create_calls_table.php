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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('initiator_id');
            $table->unsignedBigInteger('receiver_id');
            $table->enum('call_type', ['audio', 'video'])->default('video');
            $table->enum('status', ['initiated', 'ringing', 'accepted', 'rejected', 'missed', 'completed'])->default('initiated');
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->default(0); // Call duration in seconds
            $table->string('rejection_reason')->nullable(); // Reason for call rejection
            $table->timestamps();

            // Foreign keys
            $table->foreign('initiator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes for frequent queries
            $table->index('initiator_id');
            $table->index('receiver_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
