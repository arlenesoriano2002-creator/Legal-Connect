<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // Only rename if the old column exists and the new column does not exist yet
    if (Schema::hasTable('appointments')) {
        if (Schema::hasColumn('appointments', 'selected_date') && !Schema::hasColumn('appointments', 'schedule_date')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->renameColumn('selected_date', 'schedule_date');
            });
        }

        if (Schema::hasColumn('appointments', 'selected_time') && !Schema::hasColumn('appointments', 'schedule_time')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->renameColumn('selected_time', 'schedule_time');
            });
        }
    }
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->renameColumn('schedule_date', 'selected_date');
        $table->renameColumn('schedule_time', 'selected_time');
    });
}

};
