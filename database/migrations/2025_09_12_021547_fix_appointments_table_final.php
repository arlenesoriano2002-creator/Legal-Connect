<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop any conflicting columns first
            if (Schema::hasColumn('appointments', 'selected_date')) {
                $table->dropColumn('selected_date');
            }
            
            if (Schema::hasColumn('appointments', 'selected_time')) {
                $table->dropColumn('selected_time');
            }
            
            // Ensure the correct columns exist with proper constraints
            if (!Schema::hasColumn('appointments', 'schedule_date')) {
                $table->date('schedule_date')->nullable(false);
            } else {
                // Modify existing column to be not nullable
                $table->date('schedule_date')->nullable(false)->change();
            }
            
            if (!Schema::hasColumn('appointments', 'schedule_time')) {
                $table->string('schedule_time')->nullable(false);
            } else {
                // Modify existing column to be not nullable
                $table->string('schedule_time')->nullable(false)->change();
            }
            
            if (!Schema::hasColumn('appointments', 'id_front')) {
                $table->string('id_front')->nullable(false);
            } else {
                // Modify existing column to be not nullable
                $table->string('id_front')->nullable(false)->change();
            }
            
            if (!Schema::hasColumn('appointments', 'id_back')) {
                $table->string('id_back')->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'term_status')) {
                $table->string('term_status')->default('pending');
            }
            
            if (!Schema::hasColumn('appointments', 'appointment_approval')) {
                $table->string('appointment_approval')->default('pending');
            }
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Reverse the changes if needed
            $table->date('schedule_date')->nullable()->change();
            $table->string('schedule_time')->nullable()->change();
            $table->string('id_front')->nullable()->change();
        });
    }
};