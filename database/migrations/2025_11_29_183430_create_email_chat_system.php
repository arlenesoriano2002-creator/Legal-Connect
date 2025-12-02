<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only add columns if they don't exist
        if (Schema::hasTable('appointments')) {
            if (!Schema::hasColumn('appointments', 'email')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('email')->after('phone');
                });
            }
            
            if (!Schema::hasColumn('appointments', 'fullname')) {
                Schema::table('appointments', function (Blueprint $table) {
                    $table->string('fullname')->nullable();
                });
            }
        }

        // Create chattbl table if it doesn't exist
        if (!Schema::hasTable('chattbl')) {
            Schema::create('chattbl', function (Blueprint $table) {
                $table->id();
                $table->string('email')->nullable();
                $table->text('message');
                $table->string('subject')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Safe rollback
        if (Schema::hasTable('chattbl')) {
            Schema::dropIfExists('chattbl');
        }
    }
};