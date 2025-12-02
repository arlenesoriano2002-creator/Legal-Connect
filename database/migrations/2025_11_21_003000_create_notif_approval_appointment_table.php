<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifApprovalAppointment', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('email');
            $table->string('appointment_approval');
            $table->date('appointment_date')->nullable();
            $table->time('appointment_time')->nullable();
            $table->timestamps();

            $table->index(['email']);
            $table->index(['fullname']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifApprovalAppointment');
    }
};