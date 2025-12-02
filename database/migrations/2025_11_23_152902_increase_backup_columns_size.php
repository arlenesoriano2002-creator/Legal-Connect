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
        Schema::table('backups', function (Blueprint $table) {
            // Change file_name to TEXT to accommodate encrypted data
            $table->text('file_name')->change();
            
            // Change file_path to TEXT to accommodate encrypted data
            $table->text('file_path')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            // Revert back to string if needed (though you probably won't need this)
            $table->string('file_name', 255)->change();
            $table->string('file_path', 255)->change();
        });
    }
};