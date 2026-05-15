<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration removes duplicate file_path rows (keeping the earliest id)
     * and then adds a unique index on file_path to enforce uniqueness.
     *
     * WARNING: This will permanently delete duplicate rows. Review backups first.
     */
    public function up(): void
    {
        // Remove duplicate rows keeping the smallest id for each file_path
        // Works on MySQL. For other DBs, adjust the query accordingly.
        DB::statement("DELETE t1 FROM walkins_logs t1
            INNER JOIN walkins_logs t2
            WHERE t1.id > t2.id AND t1.file_path = t2.file_path");

        // Attempt to add a unique index on file_path. If it already exists, ignore the error.
        try {
            // Use raw statement to create the index (works across MySQL)
            DB::statement("ALTER TABLE `walkins_logs` ADD UNIQUE `walkins_logs_file_path_unique` (`file_path`)");
        } catch (\Throwable $e) {
            // Index likely exists or database does not support raw SQL; ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('walkins_logs', function (Blueprint $table) {
            $table->dropUnique(['file_path']);
        });
    }
};
