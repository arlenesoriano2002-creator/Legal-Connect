<?php
// Quick test to check database injection and backup logs

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

echo "=== Admin Walkins Database Test ===\n\n";

try {
    // 1. Check walkins_logs table structure
    echo "1. Walkins_logs table structure:\n";
    $columns = DB::select("SHOW COLUMNS FROM walkins_logs");
    foreach ($columns as $col) {
        echo "   - {$col->Field} ({$col->Type})\n";
    }
    
    // 2. Check existing records
    echo "\n2. Existing walkins_logs records:\n";
    $logs = DB::table('walkins_logs')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();
    
    if ($logs->count() === 0) {
        echo "   (No records)\n";
    } else {
        foreach ($logs as $log) {
            echo "   - ID: {$log->id}\n";
            echo "     Path: {$log->file_path}\n";
            echo "     Created: {$log->created_at}\n";
        }
    }
    
    // 3. Test insert
    echo "\n3. Testing database insert:\n";
    $testPath = 'admin/test_' . date('YmdHis') . '.csv';
    $testFileName = Crypt::encryptString('test_file.csv');
    
    try {
        DB::table('walkins_logs')->updateOrCreate(
            ['file_path' => $testPath],
            [
                'file_name' => $testFileName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
        echo "   ✓ Successfully inserted test record\n";
    } catch (\Exception $e) {
        echo "   ✗ Insert failed: " . $e->getMessage() . "\n";
    }
    
    // 4. Check files in directory
    echo "\n4. Files in storage/app/public/walkin_logs_files/admin:\n";
    $dir = storage_path('app/public/walkin_logs_files/admin');
    if (file_exists($dir)) {
        $files = glob($dir . '/*');
        if (empty($files)) {
            echo "   (No files)\n";
        } else {
            foreach ($files as $file) {
                echo "   - " . basename($file) . " (" . filesize($file) . " bytes)\n";
            }
        }
    } else {
        echo "   Directory not found\n";
    }
    
    // 5. Get backup logs (test the endpoint logic)
    echo "\n5. Backup logs (as would be returned by API):\n";
    $backupLogs = DB::table('walkins_logs')
        ->where('file_path', 'like', 'admin/%')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($log) {
            try {
                $log->decrypted_name = Crypt::decryptString($log->file_name);
                $log->formatted_date = Carbon::parse($log->created_at)->format('n/j/Y, g:i:s A');
                return $log;
            } catch (\Exception $e) {
                $log->decrypted_name = 'Error: ' . $e->getMessage();
                $log->formatted_date = Carbon::parse($log->created_at)->format('n/j/Y, g:i:s A');
                return $log;
            }
        });
    
    foreach ($backupLogs as $log) {
        echo "   - {$log->decrypted_name} ({$log->formatted_date})\n";
    }
    
    echo "\n=== Test Complete ===\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
