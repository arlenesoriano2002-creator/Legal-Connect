<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('chattbl', function (Blueprint $table) {
            $table->string('sender_email', 255)->nullable()->after('sender_id');
            $table->string('sender_name', 255)->nullable()->after('sender_email');
            $table->string('receiver_email', 255)->nullable()->after('receiver_id');
        });
    }

    public function down()
    {
        Schema::table('chattbl', function (Blueprint $table) {
            $table->dropColumn(['sender_email', 'sender_name', 'receiver_email']);
        });
    }
};