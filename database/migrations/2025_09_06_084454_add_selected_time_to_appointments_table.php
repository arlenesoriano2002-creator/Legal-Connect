<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'selected_time')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('selected_time')->nullable()->after('selected_date');
            });
        }
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('selected_time');
        });
    }
};