<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cordon_time_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('slot_number'); // 1-9 for 8AM-5PM
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['available', 'booked', 'unavailable', 'holiday'])
                  ->default('available');
            $table->string('slot_color')->nullable();
            $table->text('description')->nullable();
            $table->integer('appointment_id')->nullable(); // Reference to appointment if booked
            $table->timestamps();
            
            $table->unique(['date', 'slot_number']);
            $table->index(['date', 'status']);
            $table->index('appointment_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cordon_time_slots');
    }
};