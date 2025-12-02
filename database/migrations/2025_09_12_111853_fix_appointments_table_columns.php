<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, let's check if we need to migrate data from schedule_* columns to selected_* columns
        if (Schema::hasColumn('appointments', 'schedule_date') && Schema::hasColumn('appointments', 'selected_date')) {
            // Copy data from schedule_date to selected_date if selected_date is empty
            DB::statement("UPDATE appointments SET selected_date = schedule_date WHERE (selected_date IS NULL OR selected_date = '') AND schedule_date IS NOT NULL");
        }
        
        if (Schema::hasColumn('appointments', 'schedule_time') && Schema::hasColumn('appointments', 'selected_time')) {
            // Copy data from schedule_time to selected_time if selected_time is empty
            DB::statement("UPDATE appointments SET selected_time = schedule_time WHERE (selected_time IS NULL OR selected_time = '') AND schedule_time IS NOT NULL");
        }
        
        // Modify the columns to ensure they are VARCHAR and not null with proper defaults
        Schema::table('appointments', function (Blueprint $table) {
            // Change selected_date to VARCHAR if it's not already
            if (Schema::hasColumn('appointments', 'selected_date')) {
                $table->string('selected_date', 255)->nullable(false)->default('')->change();
            }
            
            // Ensure selected_time is VARCHAR
            if (Schema::hasColumn('appointments', 'selected_time')) {
                $table->string('selected_time', 255)->nullable(false)->default('')->change();
            }
            
            // Ensure id_front is VARCHAR with proper defaults
            if (Schema::hasColumn('appointments', 'id_front')) {
                $table->string('id_front', 255)->nullable(false)->default('')->change();
            }
            
            // Ensure id_back is VARCHAR with proper defaults
            if (Schema::hasColumn('appointments', 'id_back')) {
                $table->string('id_back', 255)->nullable(false)->default('')->change();
            }
            
            // Drop the schedule_date and schedule_time columns if they exist
            if (Schema::hasColumn('appointments', 'schedule_date')) {
                $table->dropColumn('schedule_date');
            }
            
            if (Schema::hasColumn('appointments', 'schedule_time')) {
                $table->dropColumn('schedule_time');
            }
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Reverse the changes if needed
            if (!Schema::hasColumn('appointments', 'schedule_date')) {
                $table->string('schedule_date', 255)->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'schedule_time')) {
                $table->string('schedule_time', 255)->nullable();
            }
            
            // Revert the column changes if necessary
            $table->string('selected_date', 255)->nullable()->change();
            $table->string('selected_time', 255)->nullable()->change();
            $table->string('id_front', 255)->nullable()->change();
            $table->string('id_back', 255)->nullable()->change();
        });
    }
};