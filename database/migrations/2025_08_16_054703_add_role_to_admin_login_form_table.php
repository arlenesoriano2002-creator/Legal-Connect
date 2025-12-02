<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('admin_login_form', function (Blueprint $table) {
            $table->string('role')->default('admin');
        });
    }

    public function down()
    {
        Schema::table('admin_login_form', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

};
