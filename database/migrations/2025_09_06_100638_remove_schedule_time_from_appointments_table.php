<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Check if the column exists before trying to remove it
            if (Schema::hasColumn('appointments', 'schedule_time')) {
                $table->dropColumn('schedule_time');
            }
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // For rollback, add the column back
            if (!Schema::hasColumn('appointments', 'schedule_time')) {
                $table->string('schedule_time')->nullable();
            }
        });
    }
};