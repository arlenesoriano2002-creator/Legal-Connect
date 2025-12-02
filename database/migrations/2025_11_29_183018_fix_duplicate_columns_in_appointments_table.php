<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if columns exist before adding them
        if (!Schema::hasColumn('appointments', 'email')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('email')->after('phone');
            });
        }

        if (!Schema::hasColumn('appointments', 'fullname')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('fullname')->nullable();
            });
        }
    }

    public function down()
    {
        // Safe rollback - don't drop columns that might have data
        Schema::table('appointments', function (Blueprint $table) {
            // Only drop if you're sure about rollback
            // $table->dropColumn(['email', 'fullname']);
        });
    }
};