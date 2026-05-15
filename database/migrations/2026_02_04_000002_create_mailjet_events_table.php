<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('mailjet_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('message_id')->nullable()->index();
            $table->string('event')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mailjet_events');
    }
};
