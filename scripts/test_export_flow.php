<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

$search = $argv[1] ?? '';
$purpose = $argv[2] ?? '';

// Count matching walkins
$query = DB::table('diffun_walkins');
if ($search !== '') {
    $query->where(function($q) use ($search) {
        $q->where('fullname', 'like', '%' . $search . '%')
          ->orWhere('address', 'like', '%' . $search . '%')
          ->orWhere('contact_number', 'like', '%' . $search . '%')
          ->orWhere('purpose', 'like', '%' . $search . '%')
          ->orWhere('branch', 'like', '%' . $search . '%');
    });
}
if ($purpose !== '') {
    $query->where('purpose', $purpose);
}
$count = $query->count();

$lastBefore = DB::table('walkins_logs')->orderBy('id', 'desc')->value('id');
$lastBefore = $lastBefore ?? 0;

echo "Matching walkins count: $count\n";
echo "Last walkins_logs id before: $lastBefore\n";

$params = ['branch' => 'cordon', 'search' => $search, 'purpose' => $purpose];
$request = Request::create('/walkins/logs/export/excel', 'POST', $params);
$controller = new App\Http\Controllers\WalkInLogsController();
try {
    $response = $controller->exportExcel($request);
    echo "Controller invoked.\n";
} catch (Throwable $e) {
    echo "Controller invocation error: " . $e->getMessage() . "\n";
}

$lastAfter = DB::table('walkins_logs')->orderBy('id', 'desc')->value('id');
$lastAfter = $lastAfter ?? 0;

echo "Last walkins_logs id after: $lastAfter\n";
if ($lastAfter > $lastBefore) {
    $row = DB::table('walkins_logs')->where('id', $lastAfter)->first();
    $decrypted = null;
    try { $decrypted = Crypt::decryptString($row->file_name); } catch (Exception $e) { $decrypted = 'Unable to decrypt: ' . $e->getMessage(); }
    echo "New DB row created (id={$row->id}) file_path={$row->file_path} decrypted_name={$decrypted}\n";
} else {
    echo "No new DB row created (export likely blocked due to no records).\n";
}
