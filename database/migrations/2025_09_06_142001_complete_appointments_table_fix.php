<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, drop the existing appointments table if it exists
        if (Schema::hasTable('appointments')) {
            Schema::drop('appointments');
        }

        // Now create the appointments table with the correct structure
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('consulting');
            $table->date('schedule_date');
            $table->string('schedule_time');
            $table->string('term_status')->default('pending');
            $table->string('id_front')->nullable();
            $table->string('id_back')->nullable();
            $table->string('appointment_approval')->default('pending');
            $table->timestamps();
        });

        // Also ensure appointment_slots table has the correct structure
        if (Schema::hasTable('appointment_slots')) {
            Schema::table('appointment_slots', function (Blueprint $table) {
                if (!Schema::hasColumn('appointment_slots', 'available_slots')) {
                    $table->integer('available_slots')->default(1)->after('time');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};