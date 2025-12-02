<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('archived_appointments', function (Blueprint $table) {
        $table->id();
        $table->string('fullname');
        $table->string('address');
        $table->string('phone');
        $table->string('email');
        $table->string('consulting');
        $table->date('selected_date')->nullable();
        $table->string('selected_time')->nullable();
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
        $table->string('schedule_time')->nullable();
        $table->string('term_status')->nullable();
        $table->string('id_front')->nullable();
        $table->string('id_back')->nullable();
        $table->string('appointment_approval')->nullable();
        $table->string('schedule_date')->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_appointments');
    }
};
