<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_tbl', function (Blueprint $table) {
            $table->id(); // auto-increment primary key
            $table->string('email')->unique(); // email column
            $table->string('password'); // password column
            $table->timestamps(); // created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_tbl');
    }
};

