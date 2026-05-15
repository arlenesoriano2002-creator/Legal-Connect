<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('case_categories') && !Schema::hasColumn('case_categories', 'service_fee')) {
            Schema::table('case_categories', function (Blueprint $table) {
                $table->decimal('service_fee', 10, 2)->nullable()->after('case_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('case_categories') && Schema::hasColumn('case_categories', 'service_fee')) {
            Schema::table('case_categories', function (Blueprint $table) {
                $table->dropColumn('service_fee');
            });
        }
    }
};
