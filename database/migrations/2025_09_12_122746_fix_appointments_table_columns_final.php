<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // First, copy data from schedule_date/schedule_time to selected_date/selected_time if needed
            if (Schema::hasColumn('appointments', 'schedule_date') && Schema::hasColumn('appointments', 'selected_date')) {
                DB::statement("UPDATE appointments SET selected_date = schedule_date WHERE selected_date IS NULL OR selected_date = ''");
            }
            
            if (Schema::hasColumn('appointments', 'schedule_time') && Schema::hasColumn('appointments', 'selected_time')) {
                DB::statement("UPDATE appointments SET selected_time = schedule_time WHERE selected_time IS NULL OR selected_time = ''");
            }
            
            // Remove the duplicate columns
            if (Schema::hasColumn('appointments', 'schedule_date')) {
                $table->dropColumn('schedule_date');
            }
            
            if (Schema::hasColumn('appointments', 'schedule_time')) {
                $table->dropColumn('schedule_time');
            }
            
            // Ensure the correct columns have the right constraints
            if (Schema::hasColumn('appointments', 'selected_date')) {
                $table->string('selected_date', 255)->nullable(false)->change();
            }
            
            if (Schema::hasColumn('appointments', 'selected_time')) {
                $table->string('selected_time', 255)->nullable(false)->change();
            }
            
            if (Schema::hasColumn('appointments', 'id_front')) {
                $table->string('id_front', 255)->nullable(false)->change();
            }
            
            if (Schema::hasColumn('appointments', 'id_back')) {
                $table->string('id_back', 255)->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add back the dropped columns if needed
            if (!Schema::hasColumn('appointments', 'schedule_date')) {
                $table->string('schedule_date', 255)->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'schedule_time')) {
                $table->string('schedule_time', 255)->nullable();
            }
        });
    }
};