<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fix appointments table
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                // Remove any duplicate columns first
                if (Schema::hasColumn('appointments', 'term_status')) {
                    $table->dropColumn('term_status');
                }
                if (Schema::hasColumn('appointments', 'id_front')) {
                    $table->dropColumn('id_front');
                }
                if (Schema::hasColumn('appointments', 'id_back')) {
                    $table->dropColumn('id_back');
                }
                if (Schema::hasColumn('appointments', 'appointment_approval')) {
                    $table->dropColumn('appointment_approval');
                }
                if (Schema::hasColumn('appointments', 'selected_date')) {
                    $table->dropColumn('selected_date');
                }
                if (Schema::hasColumn('appointments', 'selected_time')) {
                    $table->dropColumn('selected_time');
                }
                if (Schema::hasColumn('appointments', 'schedule_time')) {
                    $table->dropColumn('schedule_time');
                }

                // Add the correct columns
                if (!Schema::hasColumn('appointments', 'schedule_date')) {
                    $table->date('schedule_date')->nullable()->after('consulting');
                }
                if (!Schema::hasColumn('appointments', 'schedule_time')) {
                    $table->string('schedule_time')->nullable()->after('schedule_date');
                }
                if (!Schema::hasColumn('appointments', 'term_status')) {
                    $table->string('term_status')->nullable()->after('schedule_time');
                }
                if (!Schema::hasColumn('appointments', 'id_front')) {
                    $table->string('id_front')->nullable()->after('term_status');
                }
                if (!Schema::hasColumn('appointments', 'id_back')) {
                    $table->string('id_back')->nullable()->after('id_front');
                }
                if (!Schema::hasColumn('appointments', 'appointment_approval')) {
                    $table->string('appointment_approval')->default('pending')->after('id_back');
                }
            });
        }

        // Fix appointment_slots table if it exists
        if (Schema::hasTable('appointment_slots')) {
            // Ensure it has the correct structure
            Schema::table('appointment_slots', function (Blueprint $table) {
                if (!Schema::hasColumn('appointment_slots', 'date')) {
                    $table->date('date')->after('id');
                }
                if (!Schema::hasColumn('appointment_slots', 'time')) {
                    $table->string('time')->after('date');
                }
                if (!Schema::hasColumn('appointment_slots', 'available_slots')) {
                    $table->integer('available_slots')->default(1)->after('time');
                }
            });
        }
    }

    public function down()
    {
        // This migration is meant to fix issues, so down() might not be necessary
        // But we'll implement a basic rollback
        Schema::table('appointments', function (Blueprint $table) {
            // We won't drop columns to avoid data loss
        });
    }
};