<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('appointments') && !Schema::hasColumn('appointments', 'processed_by')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('processed_by')->nullable()->after('appointment_approval');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'processed_by')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('processed_by');
            });
        }
    }
};
