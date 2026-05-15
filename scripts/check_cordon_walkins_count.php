<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$purpose = $argv[1] ?? '';
$search = $argv[2] ?? '';

$query = DB::table('cordon_walkins');
if ($purpose !== '') {
    $query->where('purpose', $purpose);
}
if ($search !== '') {
    $query->where(function($q) use ($search) {
        $q->where('fullname', 'like', '%' . $search . '%')
          ->orWhere('address', 'like', '%' . $search . '%')
          ->orWhere('contact_number', 'like', '%' . $search . '%')
          ->orWhere('purpose', 'like', '%' . $search . '%')
          ->orWhere('branch', 'like', '%' . $search . '%');
    });
}
$count = $query->count();
echo "Cordon Count for purpose='$purpose' search='$search' => $count\n";
