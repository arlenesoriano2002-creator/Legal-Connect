<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
       Schema::table('password_resets', function (Blueprint $table) {
             $table->unsignedBigInteger('user_id')->nullable()->after('email');
            $table->string('email')->primary();
            $table->string('token');
            $table->string('new_password');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_resets');
    }
};