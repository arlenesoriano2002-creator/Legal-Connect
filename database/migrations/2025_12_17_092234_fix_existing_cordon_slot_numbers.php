<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixExistingCordonSlotNumbers extends Migration
{
    public function up()
    {
        // Reset all slot_numbers to 0 where color is null or empty
        // This ensures unconfigured slots show as empty
        DB::table('cordon_time_slots')
            ->where(function($query) {
                $query->whereNull('color')
                      ->orWhere('color', '');
            })
            ->update(['slot_number' => 0]);
            
        // Also update where slot_number = time_slot but no color is set
        DB::table('cordon_time_slots')
            ->whereRaw('slot_number = time_slot')
            ->where(function($query) {
                $query->whereNull('color')
                      ->orWhere('color', '');
            })
            ->update(['slot_number' => 0]);
    }

    public function down()
    {
        // Revert: set slot_number back to time_slot for consistency
        DB::table('cordon_time_slots')
            ->where('slot_number', 0)
            ->update(['slot_number' => DB::raw('time_slot')]);
    }
}