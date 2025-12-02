<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Models\Backup;
use Illuminate\Http\Request;
use App\Models\Appointment;

class AdminDashboardController extends Controller
{
    public function index()
{
    // Get appointment counts
    $totalAppointments = Appointment::count();
    $pendingAppointments = Appointment::where('appointment_approval', 'pending')->count();
    $approvedAppointments = Appointment::where('appointment_approval', 'approved')->count();
    $deniedAppointments = Appointment::where('appointment_approval', 'denied')->count();

    // Recent Appointments
    $recentAppointments = Appointment::orderBy('created_at', 'desc')->take(5)->get();

    // ✅ Fetch Backups for backup-manager.blade.php
    // Get backups and manually decrypt
    $backups = Backup::orderBy('created_at', 'desc')->get()->map(function($backup) {
        return (object)[
            'id' => $backup->id,
            'decrypted_file_name' => $backup->decrypted_file_name,
            'created_at' => $backup->created_at
        ];
    });

    return view('admindashboard', compact(
        'totalAppointments',
        'pendingAppointments', 
        'approvedAppointments',
        'deniedAppointments',
        'recentAppointments',
        'backups'
    ));
}
      public function createBackup()
{
    $filename = 'backup_' . now()->format('Y_m_d_His') . '.sql';
    $filePath = storage_path("app/backups/{$filename}");
    if (!file_exists(dirname($filePath))) mkdir(dirname($filePath), 0777, true);

    // MySQL dump (adjust username/password)
    $command = sprintf('mysqldump -u root legal_connect > "%s"', $filePath);
    exec($command);

    Backup::create(['file_name' => $filename, 'file_path' => $filePath]);

    return response()->json(['success' => true, 'file' => $filename]);
}

public function getBackups()
{
    return response()->json(Backup::latest()->get());
}

public function downloadBackup(Request $request)
{
    $backup = Backup::find($request->id);
    $password = $request->password;
    $zipFile = storage_path("app/backups/" . pathinfo($backup->file_name, PATHINFO_FILENAME) . ".zip");

    $zip = new ZipArchive;
    if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
        $zip->setPassword($password);
        $zip->addFile($backup->file_path, $backup->file_name);
        $zip->close();
    }

    return response()->download($zipFile)->deleteFileAfterSend(true);
}
public function refreshBackups()
{
    $backups = Backup::orderBy('created_at', 'desc')->get();

    // Render the existing partial as a full HTML chunk
    $fullHtml = view('partials.backup-manager', compact('backups'))->render();

    // Extract ONLY the backupCardsContainer div cleanly
    $dom = new \DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($fullHtml);
    libxml_clear_errors();
    
    $container = $dom->getElementById('backupCardsContainer');

    $html = $dom->saveHTML($container);

    return response()->json(['html' => $html]);
}

}