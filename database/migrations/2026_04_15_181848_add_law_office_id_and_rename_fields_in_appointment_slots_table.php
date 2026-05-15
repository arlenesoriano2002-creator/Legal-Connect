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
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->unsignedBigInteger('law_office_id')->nullable()->after('id');
            $table->foreign('law_office_id')->references('id')->on('law_offices')->onDelete('cascade');
            $table->renameColumn('time', 'time_range');
            $table->renameColumn('booked', 'capacity_remaining');
            $table->integer('capacity_remaining')->default(0)->change(); // Change type to int
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointment_slots', function (Blueprint $table) {
            $table->dropForeign(['law_office_id']);
            $table->dropColumn('law_office_id');
            $table->renameColumn('time_range', 'time');
            $table->renameColumn('capacity_remaining', 'booked');
            $table->tinyInteger('booked')->default(0)->change();
        });
    }
};
