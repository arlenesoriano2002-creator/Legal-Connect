<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixCordonTablesStructure extends Migration
{
    public function up()
    {
        // Fix cordon_time_slots table
        if (Schema::hasTable('cordon_time_slots')) {
            Schema::table('cordon_time_slots', function (Blueprint $table) {
                // Ensure unique constraint is correct
                $table->dropUnique(['date', 'time_slot']);
                
                // Add the correct unique constraint
                $table->unique(['date', 'slot_number']);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cordon_time_slots')) {
            Schema::table('cordon_time_slots', function (Blueprint $table) {
                $table->dropUnique(['date', 'slot_number']);
                $table->unique(['date', 'time_slot']);
            });
        }
    }
}