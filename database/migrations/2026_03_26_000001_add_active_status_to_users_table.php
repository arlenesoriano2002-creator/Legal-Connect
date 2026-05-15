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
            // Add active_status column to track online/offline status
            // 1 = online (user is logged in)
            // 0 = offline (user is logged out)
            // Default to 0 (offline)
            $table->tinyInteger('active_status')->default(0)->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active_status');
        });
    }
};
