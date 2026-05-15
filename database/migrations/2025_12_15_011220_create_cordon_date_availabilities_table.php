<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cordon_date_availabilities', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->enum('availability_status', ['available', 'holiday', 'unavailable'])
                  ->default('available');
            $table->string('date_color')->nullable(); // For visual representation
            $table->text('description')->nullable();
            $table->integer('total_slots')->default(9); // 9 time slots per day (8AM-5PM)
            $table->integer('booked_slots')->default(0);
            $table->timestamps();
            
            $table->index(['date', 'availability_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cordon_date_availabilities');
    }
};