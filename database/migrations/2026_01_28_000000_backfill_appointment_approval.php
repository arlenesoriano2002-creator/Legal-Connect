<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Backfill empty or NULL appointment_approval values to 'pending'.
     *
     * Note: this is a one-off normalization migration. If you prefer a reversible
     * operation, modify the down() method accordingly.
     */
    public function up()
    {
        // Update rows where appointment_approval is NULL or empty string
        DB::table('appointments')
            ->whereNull('appointment_approval')
            ->orWhere('appointment_approval', '=', '')
            ->update(['appointment_approval' => 'pending']);
    }

    /**
     * Reverse the migrations.
     * For safety, this migration does not attempt to revert values back to NULL/empty.
     */
    public function down()
    {
        // Intentionally left empty to avoid accidental data loss.
    }
};
