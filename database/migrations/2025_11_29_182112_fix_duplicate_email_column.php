<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if email column already exists in appointments table
        if (Schema::hasColumn('appointments', 'email')) {
            Schema::table('appointments', function (Blueprint $table) {
                // If it exists, make sure it's nullable
                $table->string('email')->nullable()->change();
            });
        } else {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('email')->nullable()->after('id');
            });
        }
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};