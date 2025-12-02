<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if the table exists
        if (Schema::hasTable('appointment_slots')) {
            Schema::table('appointment_slots', function (Blueprint $table) {
                // Add the available_slots column if it doesn't exist
                if (!Schema::hasColumn('appointment_slots', 'available_slots')) {
                    $table->integer('available_slots')->default(1)->after('time');
                }
                
                // You can add other missing columns or modifications here
                // For example, if you need to change column types or add indexes
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('appointment_slots')) {
            Schema::table('appointment_slots', function (Blueprint $table) {
                // Reverse the changes if needed
                if (Schema::hasColumn('appointment_slots', 'available_slots')) {
                    $table->dropColumn('available_slots');
                }
            });
        }
    }
};