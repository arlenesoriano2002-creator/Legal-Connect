<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('email_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('ran_at')->nullable();
            $table->boolean('success')->default(false);
            $table->integer('count')->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_fetch_logs');
    }
};
