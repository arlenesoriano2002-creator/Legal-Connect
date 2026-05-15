<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check for secretaries
$secretaries = DB::table('users')
    ->select('id', 'email', 'role')
    ->whereIn('role', ['secretary', 'staff', 'clerk', 'diffun_staff', 'cordon_staff'])
    ->limit(10)
    ->get();

echo "Found secretaries/staff:\n";
foreach ($secretaries as $user) {
    echo "ID: {$user->id}, Email: {$user->email}, Role: {$user->role}\n";
}

// Also check if there are any 'secretary' role users
$secretaryCount = DB::table('users')->where('role', 'secretary')->count();
echo "\nTotal secretary role users: $secretaryCount\n";
