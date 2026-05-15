<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('month_colors', function (Blueprint $table) {
    if (!Schema::hasColumn('month_colors', 'law_office_id')) {
        $table->unsignedBigInteger('law_office_id')->nullable()->after('id');
        echo "Column 'law_office_id' added successfully to 'month_colors' table.\n";
    } else {
        echo "Column 'law_office_id' already exists.\n";
    }
});
