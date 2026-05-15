<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

// Accept CLI args: search and purpose
$search = $argv[1] ?? '';
$purpose = $argv[2] ?? '';

$params = [
    'branch' => 'cordon',
    'search' => $search,
    'purpose' => $purpose,
];

$request = Request::create('/walkins/logs/export/excel', 'POST', $params);

$controller = new App\Http\Controllers\WalkInLogsController();

try {
    $response = $controller->exportExcel($request);
    echo "Export invoked with filters:\n";
    echo "search: '" . $search . "'\n";
    echo "purpose: '" . $purpose . "'\n";

    $row = DB::table('walkins_logs')->orderBy('id', 'desc')->first();
    if ($row) {
        $decrypted = null;
        try { $decrypted = Crypt::decryptString($row->file_name); } catch (Exception $e) { $decrypted = 'Unable to decrypt: ' . $e->getMessage(); }

        echo "DB Row:\n";
        echo "id: {$row->id}\n";
        echo "file_name (encrypted): {$row->file_name}\n";
        echo "file_name (decrypted): {$decrypted}\n";
        echo "file_path: {$row->file_path}\n";
        echo "created_at: {$row->created_at}\n";

        $publicPath = __DIR__ . '/../public/storage/walkin_logs_files/' . $row->file_path;
        $storagePath = __DIR__ . '/../storage/app/public/walkin_logs_files/' . $row->file_path;

        echo "Checking filesystem paths...\n";
        echo "publicPath: " . $publicPath . (file_exists($publicPath) ? " (exists)\n" : " (missing)\n");
        echo "storagePath: " . $storagePath . (file_exists($storagePath) ? " (exists)\n" : " (missing)\n");
    } else {
        echo "No DB row found in walkins_logs table.\n";
    }

} catch (Throwable $e) {
    echo "Error invoking export: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
