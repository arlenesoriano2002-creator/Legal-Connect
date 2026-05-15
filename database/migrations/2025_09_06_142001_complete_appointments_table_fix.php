<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // If the table doesn't exist, create it. Otherwise, ensure columns exist without dropping the table
        if (!Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->string('fullname');
                $table->string('address');
                $table->string('phone');
                $table->string('email');
                $table->string('consulting');
                $table->date('schedule_date');
                $table->string('schedule_time');
                $table->string('term_status')->default('pending');
                $table->string('id_front')->nullable();
                $table->string('id_back')->nullable();
                $table->string('appointment_approval')->default('pending');
                $table->timestamps();
            });
        } else {
            // Ensure necessary columns exist
            if (!Schema::hasColumn('appointments', 'fullname')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('fullname')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'address')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('address')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'phone')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('phone')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'email')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('email')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'consulting')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('consulting')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'schedule_date')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->date('schedule_date')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'schedule_time')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('schedule_time')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'term_status')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('term_status')->default('pending');
                });
            }

            if (!Schema::hasColumn('appointments', 'id_front')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('id_front')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'id_back')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('id_back')->nullable();
                });
            }

            if (!Schema::hasColumn('appointments', 'appointment_approval')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('appointment_approval')->default('pending');
                });
            }
        }

        // Also ensure appointment_slots table has the correct structure
        if (Schema::hasTable('appointment_slots')) {
            Schema::table('appointment_slots', function (Blueprint $table) {
                if (!Schema::hasColumn('appointment_slots', 'available_slots')) {
                    $table->integer('available_slots')->default(1)->after('time');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};