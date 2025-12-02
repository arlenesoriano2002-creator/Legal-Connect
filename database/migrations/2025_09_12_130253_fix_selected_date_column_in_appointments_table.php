<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, update any existing NULL values to empty string
        DB::statement("UPDATE appointments SET selected_date = '' WHERE selected_date IS NULL");
        
        // Then modify the column to not allow NULL values
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('selected_date', 255)->nullable(false)->default('')->change();
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('selected_date', 255)->nullable()->change();
        });
    }
};