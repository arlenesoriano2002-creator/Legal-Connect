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
    Schema::table('appointments', function (Blueprint $table) {
        $table->string('fullname')->nullable();
        $table->string('address')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('consulting')->nullable();
        $table->date('selected_date')->nullable();
        $table->string('selected_time')->nullable();
    });
}

public function down(): void
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropColumn([
            'fullname',
            'address',
            'phone',
            'email',
            'consulting',
            'selected_date',
            'selected_time',
        ]);
    });
}

};
