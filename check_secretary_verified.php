<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Check secretary user
$secretary = DB::table('users')
    ->where('role', 'secretary')
    ->select('id', 'email', 'role', 'is_verified', 'active_status')
    ->first();

if ($secretary) {
    echo "Secretary user:\n";
    echo "ID: " . $secretary->id . "\n";
    echo "Email: " . $secretary->email . "\n";
    echo "Role: " . $secretary->role . "\n";
    echo "Is Verified: " . ($secretary->is_verified ? 'YES' : 'NO') . "\n";
    echo "Active Status: " . $secretary->active_status . "\n";
} else {
    echo "No secretary user found\n";
}
