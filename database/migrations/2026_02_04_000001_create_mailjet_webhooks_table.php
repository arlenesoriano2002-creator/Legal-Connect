<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('mailjet_webhooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('payload');
            $table->text('headers')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mailjet_webhooks');
    }
};
