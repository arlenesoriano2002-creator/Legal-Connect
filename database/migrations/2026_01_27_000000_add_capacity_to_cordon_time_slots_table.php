<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('cordon_time_slots', 'capacity')) {
            Schema::table('cordon_time_slots', function (Blueprint $table) {
                $table->integer('capacity')->default(0)->after('slot_number');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('cordon_time_slots', 'capacity')) {
            Schema::table('cordon_time_slots', function (Blueprint $table) {
                $table->dropColumn('capacity');
            });
        }
    }
};
