<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // This makes the address field optional in the database
        $table->string('address')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('address')->nullable(false)->change();
    });
}
};
