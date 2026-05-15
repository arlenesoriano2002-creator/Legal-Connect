<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill law_office_id based on selected_branch
        DB::table('appointments')
            ->where('selected_branch', 'Diffun Branch')
            ->update(['law_office_id' => 1]);

        DB::table('appointments')
            ->where('selected_branch', 'Cordon Branch')
            ->update(['law_office_id' => 2]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally reverse by setting to null
        DB::table('appointments')
            ->whereIn('law_office_id', [1, 2])
            ->update(['law_office_id' => null]);
    }
};
