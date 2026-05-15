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
        if (!Schema::hasColumn('appointments', 'fullname')) {
            $table->string('fullname')->nullable();
        }
        if (!Schema::hasColumn('appointments', 'address')) {
            $table->string('address')->nullable();
        }
        if (!Schema::hasColumn('appointments', 'phone')) {
            $table->string('phone')->nullable();
        }
        if (!Schema::hasColumn('appointments', 'email')) {
            $table->string('email')->nullable();
        }
        if (!Schema::hasColumn('appointments', 'consulting')) {
            $table->string('consulting')->nullable();
        }
        if (!Schema::hasColumn('appointments', 'selected_date')) {
            $table->date('selected_date')->nullable();
        }
        if (!Schema::hasColumn('appointments', 'selected_time')) {
            $table->string('selected_time')->nullable();
        }
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
