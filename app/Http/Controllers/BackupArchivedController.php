<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use App\Models\Backup;
use App\Models\Appointment;
use Barryvdh\DomPDF\Facade\Pdf;

class BackupArchivedController extends Controller
{
    public function getBackups()
    {
        $backups = Backup::orderBy('created_at', 'desc')->get();
        
        // Return backups with decrypted file names and paths
        return $backups->map(function($backup) {
            return [
                'id' => $backup->id,
                'file_name' => $backup->decrypted_file_name, // Use accessor
                'file_path' => $backup->decrypted_file_path, // Use accessor
                'created_at' => $backup->created_at
            ];
        });
    }

public function deleteBackup($filename)
{
    try {
        // Since filenames are encrypted in DB, we need to find by decrypted name
        $backup = Backup::all()->first(function($backup) use ($filename) {
            return $backup->decrypted_file_name === $filename;
        });
        
        if ($backup) {
            $actualFilePath = $backup->decrypted_file_path;
            Storage::disk('local')->delete($actualFilePath);
            $backup->delete();
            
            return redirect()->back()->with('success', 'Backup deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Backup not found.');
        }
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to delete backup: ' . $e->getMessage());
    }
}

    public function downloadBackupAsPdf($filename)
    {
        // Load archived appointments (this is likely what you want in the backup)
        $appointments = \App\Models\ArchivedAppointment::orderBy('id', 'asc')->get();

        if ($appointments->isEmpty()) {
            return back()->with('error', 'No archived appointments to export.');
        }

        // Convert to readable text
        $data = "";
        foreach ($appointments as $a) {
            $data .=
    "------------------------------
    Full Name:       {$a->fullname}
    Address:         {$a->address}
    Phone:           {$a->phone}
    Email:           {$a->email}
    Consulting:      {$a->consulting}
    Date:            {$a->selected_date}
    Time:            {$a->selected_time}
    Terms:           {$a->term_status}
    Approval:        {$a->appointment_approval}
    ------------------------------
    ";
        }

        // Generate PDF from view
        $pdf = \PDF::loadView('pdf.backup', compact('data'))->setPaper('a4', 'portrait');

        return $pdf->download('ArchivedBackup_' . date('Y_m_d_His') . '.pdf');
    }

    // NEW METHOD: Create SQL backup based on filter
    public function createAppointmentsBackup(Request $request)
    {
        try {
            $filter = $request->input('filter', 'all');
            
            // Query appointments based on filter
            $query = Appointment::query();
            
            if ($filter !== 'all') {
                $query->where('appointment_approval', $filter);
            }
            
            $appointments = $query->get();
            
            if ($appointments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No appointments found for the selected filter.'
                ], 404);
            }

            // Generate SQL content
            $sqlContent = $this->generateSqlContent($appointments, $filter);
            
            // Generate filename
            $filename = $this->generateFilename($filter);
            $filePath = "backups/{$filename}";
            
            // Ensure backups directory exists
            Storage::disk('local')->makeDirectory('backups');
            
            // Save SQL file to storage
            Storage::disk('local')->put($filePath, $sqlContent);
            
            // Encrypt the file_name and file_path before storing in database
            $encryptedFileName = Crypt::encryptString($filename);
            $encryptedFilePath = Crypt::encryptString($filePath);
            
            // Check if encrypted values are too long (for debugging)
            if (strlen($encryptedFileName) > 65535 || strlen($encryptedFilePath) > 65535) {
                throw new \Exception('Encrypted data too long for database column');
            }
            
            // Insert record into backups table with encrypted file_name and file_path
            Backup::create([
                'file_name' => $encryptedFileName,
                'file_path' => $encryptedFilePath,
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully.',
                'filename' => $filename,
                'file_path' => $filePath
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Backup creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showBackups()
    {
        $backups = Backup::orderBy('created_at', 'desc')->get();
        
        // Pass decrypted data to the view
        $decryptedBackups = $backups->map(function($backup) {
            return [
                'id' => $backup->id,
                'file_name' => $backup->decrypted_file_name,
                'file_path' => $backup->decrypted_file_path,
                'created_at' => $backup->created_at
            ];
        });
        
        return view('backups.index', ['backups' => $decryptedBackups]);
    }

    // NEW METHOD: Download backup file
    public function downloadBackupFile($backupId)
    {
        try {
            $backup = Backup::findOrFail($backupId);
            
            // Use the accessor to get the decrypted file path and name
            $actualFilePath = $backup->decrypted_file_path;
            $actualFileName = $backup->decrypted_file_name;
            
            if (!Storage::disk('local')->exists($actualFilePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found in storage.'
                ], 404);
            }
            
            return Storage::disk('local')->download($actualFilePath, $actualFileName);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download backup: ' . $e->getMessage()
            ], 500);
        }
    }

    // Generate SQL content from appointments
    private function generateSqlContent($appointments, $filter)
    {
        $sql = "-- LegalConnect Appointments Backup\n";
        $sql .= "-- Filter: {$filter}\n";
        $sql .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n";
        $sql .= "-- Total Records: " . $appointments->count() . "\n\n";
        
        foreach ($appointments as $appointment) {
            $sql .= $this->generateInsertStatement($appointment);
        }
        
        return $sql;
    }

    // Generate INSERT statement for a single appointment
    private function generateInsertStatement($appointment)
    {
        $columns = [
            'id', 'fullname', 'address', 'phone', 'email', 'consulting',
            'selected_date', 'selected_time', 'term_status', 'appointment_approval',
            'id_front', 'id_back', 'created_at', 'updated_at'
        ];
        
        $values = [];
        foreach ($columns as $column) {
            $value = $appointment->$column;
            
            if ($value === null) {
                $values[] = 'NULL';
            } elseif (is_numeric($value)) {
                $values[] = $value;
            } else {
                // Escape single quotes for SQL
                $escapedValue = str_replace("'", "''", $value);
                $values[] = "'{$escapedValue}'";
            }
        }
        
        $valuesStr = implode(', ', $values);
        
        return "INSERT INTO appointments (" . implode(', ', $columns) . ") VALUES ({$valuesStr});\n";
    }

    // Generate filename based on filter and timestamp
    private function generateFilename($filter)
    {
        $timestamp = now()->format('Y_m_d_His');
        return "{$filter}_{$timestamp}.sql";
    }
    public function deleteBackupById($id)
{
    try {
        $backup = Backup::findOrFail($id);
        
        // Use the accessor to get the decrypted file path
        $actualFilePath = $backup->decrypted_file_path;
        Storage::disk('local')->delete($actualFilePath);
        $backup->delete();
        
        return redirect()->back()->with('success', 'Backup deleted successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Failed to delete backup: ' . $e->getMessage());
    }
}

public function getDecryptedBackups()
{
    $backups = Backup::orderBy('created_at', 'desc')->get();
    
    // Return backups with decrypted file names as objects
    return $backups->map(function($backup) {
        return (object)[
            'id' => $backup->id,
            'file_name' => $backup->file_name, // Keep original encrypted for reference
            'decrypted_file_name' => $backup->decrypted_file_name, // Add decrypted
            'file_path' => $backup->file_path, // Keep original encrypted for reference
            'decrypted_file_path' => $backup->decrypted_file_path, // Add decrypted
            'created_at' => $backup->created_at
        ];
    });
}
// Add this new method to BackupArchivedController.php
public function createAppointmentsBackupPdf(Request $request)
{
    try {
        $filter = $request->input('filter', 'all');
        
        // Query appointments based on filter
        $query = Appointment::query();
        
        if ($filter !== 'all') {
            $query->where('appointment_approval', $filter);
        }
        
        $appointments = $query->get();
        
        if ($appointments->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No appointments found for the selected filter.'
            ], 404);
        }

        // Generate PDF content
        $pdf = $this->generatePdfContent($appointments, $filter);
        
        // Generate filename
        $filename = $this->generatePdfFilename($filter);
        $filePath = "backups/{$filename}";
        
        // Ensure backups directory exists
        Storage::disk('local')->makeDirectory('backups');
        
        // Save PDF file to storage
        Storage::disk('local')->put($filePath, $pdf->output());
        
        // Encrypt the file_name and file_path before storing in database
        $encryptedFileName = Crypt::encryptString($filename);
        $encryptedFilePath = Crypt::encryptString($filePath);
        
        // Check if encrypted values are too long (for debugging)
        if (strlen($encryptedFileName) > 65535 || strlen($encryptedFilePath) > 65535) {
            throw new \Exception('Encrypted data too long for database column');
        }
        
        // Insert record into backups table with encrypted file_name and file_path
        Backup::create([
            'file_name' => $encryptedFileName,
            'file_path' => $encryptedFilePath,
            'created_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'PDF backup created successfully.',
            'filename' => $filename,
            'file_path' => $filePath
        ]);
        
    } catch (\Exception $e) {
        \Log::error('PDF Backup creation failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to create PDF backup: ' . $e->getMessage()
        ], 500);
    }
}

// Generate PDF content
private function generatePdfContent($appointments, $filter)
{
    $data = [
        'appointments' => $appointments,
        'filter' => $filter,
        'generated_at' => now()->format('Y-m-d H:i:s'),
        'total_records' => $appointments->count()
    ];

    // Generate PDF from view with table format
    return PDF::loadView('pdf.appointments_table', $data)->setPaper('a4', 'landscape');
}

// Generate PDF filename
private function generatePdfFilename($filter)
{
    $timestamp = now()->format('Y_m_d_His');
    return "{$filter}_appointments_{$timestamp}.pdf";
}
public function viewBackupFile($backupId)
{
    try {
        $backup = Backup::findOrFail($backupId);
        
        // Use the accessor to get the decrypted file path and name
        $actualFilePath = $backup->decrypted_file_path;
        $actualFileName = $backup->decrypted_file_name;
        
        if (!Storage::disk('local')->exists($actualFilePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found in storage.'
            ], 404);
        }
        
        // Return the PDF file with inline content disposition
        return response()->file(
            Storage::disk('local')->path($actualFilePath),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $actualFileName . '"'
            ]
        );
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to view backup: ' . $e->getMessage()
        ], 500);
    }
}
}