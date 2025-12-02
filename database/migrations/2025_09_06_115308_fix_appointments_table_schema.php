<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Check if columns exist before trying to add them
            if (!Schema::hasColumn('appointments', 'term_status')) {
                $table->string('term_status')->nullable()->after('consulting');
            }
            
            if (!Schema::hasColumn('appointments', 'id_front')) {
                $table->string('id_front')->nullable()->after('term_status');
            }
            
            if (!Schema::hasColumn('appointments', 'id_back')) {
                $table->string('id_back')->nullable()->after('id_front');
            }
            
            if (!Schema::hasColumn('appointments', 'appointment_approval')) {
                $table->string('appointment_approval')->default('pending')->after('id_back');
            }
            
            // Check if we need to rename columns
            if (Schema::hasColumn('appointments', 'selected_date') && !Schema::hasColumn('appointments', 'schedule_date')) {
                $table->renameColumn('selected_date', 'schedule_date');
            }
            
            if (Schema::hasColumn('appointments', 'selected_time') && !Schema::hasColumn('appointments', 'schedule_time')) {
                $table->renameColumn('selected_time', 'schedule_time');
            }
            
            // Make sure schedule_date and schedule_time columns exist
            if (!Schema::hasColumn('appointments', 'schedule_date')) {
                $table->date('schedule_date')->nullable()->after('consulting');
            }
            
            if (!Schema::hasColumn('appointments', 'schedule_time')) {
                $table->string('schedule_time')->nullable()->after('schedule_date');
            }
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Don't drop columns in down method to avoid data loss
            // You can reverse renames if needed
            if (Schema::hasColumn('appointments', 'schedule_date') && !Schema::hasColumn('appointments', 'selected_date')) {
                $table->renameColumn('schedule_date', 'selected_date');
            }
            
            if (Schema::hasColumn('appointments', 'schedule_time') && !Schema::hasColumn('appointments', 'selected_time')) {
                $table->renameColumn('schedule_time', 'selected_time');
            }
        });
    }
};