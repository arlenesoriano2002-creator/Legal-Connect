<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// ✅ Automatic Database Backup (works online + emails admin link)
Artisan::command('backup:now', function () {
    $this->info('🔄 Starting secure database backup...');

    $timestamp = date('Y_m_d_His');
    $sqlFilename = "backup_{$timestamp}.sql";
    $backupDir = storage_path('app/backups');
    $sqlPath = "{$backupDir}/{$sqlFilename}";

    if (!file_exists($backupDir)) {
        mkdir($backupDir, 0777, true);
    }

    $db   = env('DB_DATABASE');
    $user = env('DB_USERNAME');
    $pass = env('DB_PASSWORD');
    $host = env('DB_HOST', '127.0.0.1');

    if (stripos(PHP_OS, 'WIN') === 0) {
        $mysqldump = 'C:\xampp\mysql\bin\mysqldump.exe';
        $command = "\"{$mysqldump}\" -u{$user} -p{$pass} -h{$host} {$db} > \"{$sqlPath}\"";
    } else {
        $command = "mysqldump -u{$user} -p'{$pass}' -h{$host} {$db} > \"{$sqlPath}\"";
    }

    @system($command);

    // Zip the backup file
    $zipFilename = "{$sqlFilename}.zip";
    $zipPath = "{$backupDir}/{$zipFilename}";
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($sqlPath, $sqlFilename);
        $zip->close();
        unlink($sqlPath); // delete the uncompressed SQL
    }

    $this->info("✅ Backup created: {$zipPath}");

    // Generate admin download link
    $downloadUrl = URL::to('/') . route('admin.download.backup', ['filename' => basename($zipPath)], false);

    // Send email to admin
    $adminEmail = env('ADMIN_EMAIL');
    if ($adminEmail) {
        try {
            Mail::send([], [], function ($message) use ($adminEmail, $downloadUrl, $zipFilename) {
                $message->to($adminEmail)
                        ->subject("📦 LegalConnect Backup Ready")
                        ->setBody("
                            <p>Hello Admin,</p>
                            <p>A new system backup has been created successfully.</p>
                            <p><strong>Filename:</strong> {$zipFilename}</p>
                            <p>You can securely download it here (admin login required):</p>
                            <p><a href='{$downloadUrl}'>{$downloadUrl}</a></p>
                            <p>— LegalConnect System</p>
                        ", 'text/html');
            });

            $this->info("📧 Backup email sent to {$adminEmail}");
        } catch (Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
        }
    }
})->purpose('Backup DB, zip it, and email admin link');
