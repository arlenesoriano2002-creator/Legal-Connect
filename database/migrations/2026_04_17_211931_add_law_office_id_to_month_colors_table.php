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
        Schema::table('month_colors', function (Blueprint $table) {
            if (!Schema::hasColumn('month_colors', 'law_office_id')) {
                $table->unsignedBigInteger('law_office_id')->nullable()->after('id');
                $table->foreign('law_office_id')->references('id')->on('law_offices')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('month_colors', function (Blueprint $table) {
            if (Schema::hasColumn('month_colors', 'law_office_id')) {
                $table->dropForeign(['law_office_id']);
                $table->dropColumn('law_office_id');
            }
        });
    }
};
