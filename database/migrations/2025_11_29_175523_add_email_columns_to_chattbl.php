<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('chattbl', function (Blueprint $table) {
            $table->string('sender_email')->nullable()->after('sender_id');
            $table->string('receiver_email')->nullable()->after('receiver_id');
            $table->enum('message_type', ['incoming', 'outgoing'])->default('incoming')->after('sender_role');
        });
    }

    public function down()
    {
        Schema::table('chattbl', function (Blueprint $table) {
            $table->dropColumn(['sender_email', 'receiver_email', 'message_type']);
        });
    }
};