<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up()
{
    if (!Schema::hasColumn('appointments', 'email')) {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('email')->nullable()->after('id');
        });
    }
}

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
