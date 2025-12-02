<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('admin_login_form')) {
            Schema::create('admin_login_form', function (Blueprint $table) {
                $table->id();
                $table->string('username');
                $table->string('password');
                $table->timestamps();
            });
        }
    }


    public function down(): void
    {
        Schema::dropIfExists('admin_login_form');
    }
};
