<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                // Add selected_date if it doesn't exist
                if (!Schema::hasColumn('appointments', 'selected_date')) {
                    $table->string('selected_date')->nullable()->after('schedule_time');
                }
                
                // Add selected_time if it doesn't exist
                if (!Schema::hasColumn('appointments', 'selected_time')) {
                    $table->string('selected_time')->nullable()->after('selected_date');
                }
                
                // Add category if it doesn't exist
                if (!Schema::hasColumn('appointments', 'category')) {
                    $table->string('category')->nullable()->after('consulting');
                }
                
                // Add case_name if it doesn't exist
                if (!Schema::hasColumn('appointments', 'case_name')) {
                    $table->string('case_name')->nullable()->after('category');
                }
                
                // Add selected_branch if it doesn't exist
                if (!Schema::hasColumn('appointments', 'selected_branch')) {
                    $table->string('selected_branch')->nullable()->after('case_name');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                $columns = [
                    'selected_date',
                    'selected_time',
                    'category',
                    'case_name',
                    'selected_branch'
                ];
                
                foreach ($columns as $column) {
                    if (Schema::hasColumn('appointments', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
