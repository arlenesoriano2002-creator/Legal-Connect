<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Remove this line to prevent adding schedule_time again
            // $table->string('schedule_time')->nullable();
            
            // Keep only these columns
            $table->string('term_status')->nullable();
            $table->string('id_front')->nullable();
            $table->string('id_back')->nullable();
            $table->string('appointment_approval')->default('pending');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'term_status',
                'id_front',
                'id_back',
                'appointment_approval'
            ]);
        });
    }
};