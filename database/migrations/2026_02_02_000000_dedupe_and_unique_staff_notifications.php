<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DedupeAndUniqueStaffNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration removes duplicate rows in staff_notifications (keeping the
     * lowest id per staff_id+appointment_id) and then adds a UNIQUE constraint
     * to prevent future duplicates.
     */
    public function up()
    {
        // Delete duplicates keeping the row with the smallest id per (staff_id, appointment_id)
        // Works on MySQL / MariaDB
        DB::statement('DELETE n1 FROM staff_notifications n1 INNER JOIN staff_notifications n2 ON n1.staff_id = n2.staff_id AND n1.appointment_id = n2.appointment_id AND n1.id > n2.id');

        // Add unique index to enforce uniqueness at DB level
        Schema::table('staff_notifications', function (Blueprint $table) {
            $table->unique(['staff_id', 'appointment_id'], 'staff_notifications_staffid_appointmentid_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('staff_notifications', function (Blueprint $table) {
            $table->dropUnique('staff_notifications_staffid_appointmentid_unique');
        });
    }
}
