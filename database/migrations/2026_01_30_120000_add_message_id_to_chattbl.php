<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds a nullable message_id column with an index if it doesn't exist.
     */
    public function up(): void
    {
        if (!Schema::hasTable('chattbl')) {
            // If table doesn't exist, nothing to do (guard clause)
            return;
        }

        if (!Schema::hasColumn('chattbl', 'message_id')) {
            Schema::table('chattbl', function (Blueprint $table) {
                $table->string('message_id')->nullable()->index()->after('message');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chattbl') && Schema::hasColumn('chattbl', 'message_id')) {
            Schema::table('chattbl', function (Blueprint $table) {
                $table->dropIndex(['message_id']);
                $table->dropColumn('message_id');
            });
        }
    }
};
