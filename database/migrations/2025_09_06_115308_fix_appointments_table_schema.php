<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add supplemental columns if they don't exist
        if (Schema::hasTable('appointments')) {
            if (!Schema::hasColumn('appointments', 'term_status')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('term_status')->nullable()->after('consulting');
                });
            }

            if (!Schema::hasColumn('appointments', 'id_front')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('id_front')->nullable()->after('term_status');
                });
            }

            if (!Schema::hasColumn('appointments', 'id_back')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('id_back')->nullable()->after('id_front');
                });
            }

            if (!Schema::hasColumn('appointments', 'appointment_approval')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('appointment_approval')->default('pending')->after('id_back');
                });
            }

            // Rename columns if needed
            if (Schema::hasColumn('appointments', 'selected_date') && !Schema::hasColumn('appointments', 'schedule_date')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->renameColumn('selected_date', 'schedule_date');
                });
            }

            if (Schema::hasColumn('appointments', 'selected_time') && !Schema::hasColumn('appointments', 'schedule_time')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->renameColumn('selected_time', 'schedule_time');
                });
            }

            // Ensure schedule_date and schedule_time columns exist (guarded)
            if (!Schema::hasColumn('appointments', 'schedule_date')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->date('schedule_date')->nullable()->after('consulting');
                });
            }

            if (!Schema::hasColumn('appointments', 'schedule_time')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('schedule_time')->nullable()->after('schedule_date');
                });
            }
        }
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Don't drop columns in down method to avoid data loss
            // You can reverse renames if needed
            if (Schema::hasColumn('appointments', 'schedule_date') && !Schema::hasColumn('appointments', 'selected_date')) {
                $table->renameColumn('schedule_date', 'selected_date');
            }
            
            if (Schema::hasColumn('appointments', 'schedule_time') && !Schema::hasColumn('appointments', 'selected_time')) {
                $table->renameColumn('schedule_time', 'selected_time');
            }
        });
    }
};