<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create Cordon Date Availabilities Table
        Schema::create('cordon_date_availabilities', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('availability_status')->default('available');
            $table->string('date_color')->nullable(); // red, orange, green
            $table->string('description')->nullable();
            $table->integer('total_slots')->default(9);
            $table->integer('booked_slots')->default(0);
            $table->timestamps();
            
            $table->index(['date']);
            $table->index(['date', 'availability_status']);
        });

        // Create Cordon Time Slots Table
        Schema::create('cordon_time_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('slot_number'); // 1-9
            $table->time('start_time');
            $table->time('end_time');
            $table->string('status')->default('available'); // available, booked, unavailable
            $table->string('slot_color')->nullable(); // red, green
            $table->string('description')->nullable();
            $table->timestamps();
            
            $table->unique(['date', 'slot_number']);
            $table->index(['date', 'status']);
            $table->index(['date', 'slot_color']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cordon_time_slots');
        Schema::dropIfExists('cordon_date_availabilities');
    }
};