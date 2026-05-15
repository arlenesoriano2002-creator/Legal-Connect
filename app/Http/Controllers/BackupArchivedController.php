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
                $query->whereRaw("LOWER(TRIM(appointment_approval)) = ?", [strtolower(trim($filter))]);
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
        // Remove 'id' from columns list
        $columns = [
            'fullname', 'address', 'phone', 'email', 'consulting',
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
        // Get the filtered appointments array from the frontend
        $appointmentsData = $request->input('appointments', []);
        $filters = $request->input('filters', []);
        
        if (empty($appointmentsData)) {
            return response()->json([
                'success' => false,
                'message' => 'No appointments to export.'
            ], 400);
        }

        // Convert the array of appointment data to objects that match Appointment model structure
        $appointments = collect($appointmentsData);
        
        // Generate PDF content with filter info
        $filterSummary = $this->buildFilterSummary($filters);
        $pdf = $this->generatePdfContentFromArray($appointments, $filterSummary, $filters);
        
        // Generate filename with timestamp
        $filename = $this->generatePdfFilenameFromFilters($filters);
        $filePath = "backups/{$filename}";
        
        // Ensure backups directory exists and save file
        Storage::disk('local')->makeDirectory('backups');
        Storage::disk('local')->put($filePath, $pdf->output());
        
        // Encrypt and store in database
        $encryptedFileName = Crypt::encryptString($filename);
        $encryptedFilePath = Crypt::encryptString($filePath);
        
        if (strlen($encryptedFileName) > 65535 || strlen($encryptedFilePath) > 65535) {
            throw new \Exception('Encrypted data too long for database column');
        }
        
        Backup::create([
            'file_name' => $encryptedFileName,
            'file_path' => $encryptedFilePath,
            'created_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'PDF backup created successfully.',
            'filename' => $filename,
            'file_path' => $filePath,
            'records_exported' => $appointments->count()
        ]);
        
    } catch (\Exception $e) {
        \Log::error('PDF Backup creation failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to create PDF backup: ' . $e->getMessage()
        ], 500);
    }
}

// Helper method: Build filter summary string
private function buildFilterSummary($filters)
{
    $parts = [];
    
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $parts[] = "Status: " . ucfirst($filters['status']);
    }
    
    if (!empty($filters['category']) && $filters['category'] !== 'all') {
        $parts[] = "Category: " . $filters['category'];
    }
    
    if (!empty($filters['caseName']) && $filters['caseName'] !== 'all') {
        $parts[] = "Case: " . $filters['caseName'];
    }
    
    if (!empty($filters['branch']) && $filters['branch'] !== 'all') {
        $parts[] = "Branch: " . $filters['branch'];
    }
    
    return !empty($parts) ? implode(' | ', $parts) : 'All Appointments';
}

// Helper method: Generate PDF content from filtered array
private function generatePdfContentFromArray($appointments, $filterSummary, $filter)
{
    $data = [
        'appointments' => $appointments,
        'filter_summary' => $filterSummary,
        'filter' => $filter, // Pass filter to Blade template
        'generated_at' => now()->format('Y-m-d H:i:s'),
        'total_records' => $appointments->count()
    ];

    return PDF::loadView('pdf.appointments_table', $data)->setPaper('a4', 'landscape');
}

// Helper method: Generate PDF filename from filters
private function generatePdfFilenameFromFilters($filters)
{
    $timestamp = now()->format('Y_m_d_His');
    $filterParts = [];
    
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $filterParts[] = strtolower($filters['status']);
    }
    
    if (!empty($filters['category']) && $filters['category'] !== 'all') {
        $filterParts[] = 'cat_' . strtolower(str_replace(' ', '', $filters['category']));
    }
    
    $filterSuffix = !empty($filterParts) ? '_' . implode('_', $filterParts) : '';
    return "appointments{$filterSuffix}_{$timestamp}.pdf";
}
// Generate PDF content
private function generatePdfContent($appointments, $filter, $branch = null)
{
    // Transform appointments to exclude ID
    $appointmentsWithoutId = $appointments->map(function($appointment) {
        return [
            'fullname' => $appointment->fullname,
            'email' => $appointment->email,
            'phone' => $appointment->phone,
            'address' => $appointment->address,
            'category' => $appointment->category,
            'case_name' => $appointment->case_name,
            'branch' => $appointment->selected_branch, // CHANGE THIS: $appointment->branch -> $appointment->selected_branch
            'selected_date' => $appointment->selected_date,
            'selected_time' => $appointment->selected_time,
            'appointment_approval' => $appointment->appointment_approval,
            'term_status' => $appointment->term_status,
            'created_at' => $appointment->created_at,
            'updated_at' => $appointment->updated_at,
        ];
    });


    $data = [
        'appointments' => $appointmentsWithoutId,
        'filter' => $filter,
        'branch' => $branch, // Add this line
        'generated_at' => now()->format('Y-m-d H:i:s'),
        'total_records' => $appointments->count()
    ];

    // Generate PDF from view with table format
    return PDF::loadView('pdf.appointments_table', $data)->setPaper('a4', 'landscape');
}
// Generate PDF filename
private function generatePdfFilename($filter, $branch = null)
{
    $timestamp = now()->format('Y_m_d_His');
    $branchSuffix = $branch ? "_{$branch}" : '';
    return "{$filter}_appointments{$branchSuffix}_{$timestamp}.pdf";
}

public function viewBackupFile($backupId, Request $request)
{
    \Log::info('viewBackupFile called', [
        'backupId' => $backupId,
        'format' => $request->get('format'),
        'inline' => $request->get('inline'),
        'url' => $request->fullUrl()
    ]);
    
    try {
        $backup = Backup::find($backupId);
        
        if (!$backup) {
            \Log::error('Backup not found: ' . $backupId);
            return response()->json([
                'success' => false,
                'message' => 'Backup not found.'
            ], 404);
        }
        
        \Log::info('Backup found', [
            'id' => $backup->id,
            'decrypted_file_name' => $backup->decrypted_file_name,
            'decrypted_file_path' => $backup->decrypted_file_path
        ]);
        
        $decryptedFilePath = $backup->decrypted_file_path;
        
        if (!Storage::disk('local')->exists($decryptedFilePath)) {
            \Log::error('File not found in storage: ' . $decryptedFilePath);
            return response()->json([
                'success' => false,
                'message' => 'Backup file not found in storage.'
            ], 404);
        }
        
        $filePath = Storage::disk('local')->path($decryptedFilePath);
        $fileName = $backup->decrypted_file_name;
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Check if format=json is requested for CSV/Excel preview
        if ($request->get('format') === 'json') {
            \Log::info('Returning JSON format for CSV preview');
            return $this->getCsvDataAsJson($backup);
        }
        
        // For PDF with inline=true, return inline content
        if ($extension === 'pdf' && $request->get('inline')) {
            \Log::info('Returning PDF inline');
            $headers = [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $fileName . '"',
                'Cache-Control' => 'public, max-age=3600',
            ];
            
            return response()->file($filePath, $headers);
        }
        
        // For text files with inline=true, return text content
        if (in_array($extension, ['txt', 'sql', 'log']) && $request->get('inline')) {
            \Log::info('Returning text file inline');
            $content = Storage::disk('local')->get($decryptedFilePath);
            
            // Limit content for preview
            $maxLength = 100000;
            if (strlen($content) > $maxLength) {
                $content = substr($content, 0, $maxLength) . "\n\n[Preview truncated - download file to see full content]";
            }
            
            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
        }
        
        // Default: force download
        \Log::info('Returning file for download');
        return Storage::disk('local')->download($decryptedFilePath, $fileName);
        
    } catch (\Exception $e) {
        \Log::error('Error in viewBackupFile: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        
        // Return JSON error for AJAX requests
        if ($request->expectsJson() || $request->get('format') === 'json') {
            return response()->json([
                'success' => false,
                'message' => 'Failed to view backup: ' . $e->getMessage()
            ], 500);
        }
        
        // Return plain error for regular requests
        return response('Failed to view backup: ' . $e->getMessage(), 500);
    }
}
public function viewCsvBackup($backupId)
{
    try {
        $backup = Backup::findOrFail($backupId);

        // ✅ Adjust this if needed (storage or public)
        $filePath = storage_path('app/' . $backup->file_path);
        // OR if stored publicly:
        // $filePath = public_path($backup->file_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'CSV file not found'
            ], 404);
        }

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }

        return response()->json([
            'status' => 'success',
            'headers' => $rows[0] ?? [],
            'rows' => array_slice($rows, 1)
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}
// NEW METHOD: Get CSV data as JSON for preview
private function getCsvDataAsJson($backup)
{
    try {
        // First, get the actual file path from storage
        $decryptedFilePath = $backup->decrypted_file_path;
        
        // Check if the path exists in storage
        if (!Storage::disk('local')->exists($decryptedFilePath)) {
            \Log::error('File not found in storage: ' . $decryptedFilePath);
            return response()->json(['error' => 'CSV file not found in storage'], 404);
        }
        
        // Get the full path
        $filePath = Storage::disk('local')->path($decryptedFilePath);
        
        \Log::info('Attempting to parse CSV file', [
            'backup_id' => $backup->id,
            'file_path' => $filePath,
            'file_exists' => file_exists($filePath),
            'file_size' => filesize($filePath)
        ]);
        
        $extension = pathinfo($backup->decrypted_file_name, PATHINFO_EXTENSION);
        
        if ($extension === 'csv') {
            return $this->parseCsvFile($filePath);
        } elseif ($extension === 'xlsx') {
            return $this->parseExcelFile($filePath);
        } else {
            return response()->json(['error' => 'Unsupported file format for preview: ' . $extension], 400);
        }
        
    } catch (\Exception $e) {
        \Log::error('Error in getCsvDataAsJson: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json(['error' => 'Failed to parse file: ' . $e->getMessage()], 500);
    }
}
// Helper method to parse CSV
private function parseCsvFile($filePath)
{
    $rows = [];
    $headers = [];
    
    \Log::info('Parsing CSV file: ' . $filePath);
    
    if (($handle = fopen($filePath, 'r')) !== false) {
        // Read the entire file to analyze structure
        $allLines = [];
        while (($line = fgets($handle)) !== false) {
            $allLines[] = $line;
        }
        fclose($handle);
        
        \Log::info('Total lines in CSV: ' . count($allLines));
        
        // Reopen for CSV parsing
        $handle = fopen($filePath, 'r');
        
        // Skip metadata lines - look for header pattern
        $headerFound = false;
        $lineNumber = 0;
        
        while (($row = fgetcsv($handle)) !== false && !$headerFound) {
            $lineNumber++;
            
            // Check if this row looks like headers (contains "Full Name", "Email", etc.)
            $rowString = implode(' ', $row);
            
            if (str_contains($rowString, 'Full Name') && 
                str_contains($rowString, 'Email') &&
                str_contains($rowString, 'Phone')) {
                
                $headers = $row;
                $headerFound = true;
                \Log::info('Found headers at line ' . $lineNumber, $headers);
                
                // Clean up headers
                $headers = array_map(function($header) {
                    // Remove ### and any special characters
                    $header = preg_replace('/^#+\s*/', '', $header);
                    $header = preg_replace('/\s+/', ' ', $header);
                    return trim($header);
                }, $headers);
                
                break;
            }
            
            // Skip first few lines if they don't contain headers
            if ($lineNumber > 10) {
                // If we can't find headers in first 10 lines, assume first line is headers
                fseek($handle, 0);
                $headers = fgetcsv($handle);
                $headerFound = true;
                \Log::info('Using first line as headers', $headers);
                break;
            }
        }
        
        if (!$headerFound) {
            fclose($handle);
            return response()->json(['error' => 'Could not find CSV headers'], 400);
        }
        
        // Read data rows
        $count = 0;
        $maxRows = 500;
        
        while (($data = fgetcsv($handle)) !== false && $count < $maxRows) {
            // Skip rows that are clearly metadata or separators
            $firstCell = $data[0] ?? '';
            if (str_starts_with(trim($firstCell), '#') || 
                str_starts_with(trim($firstCell), '---') ||
                empty(trim(implode('', $data)))) {
                continue;
            }
            
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $data[$index] ?? '';
            }
            
            // Only add non-empty rows
            if (!empty(array_filter($row))) {
                $rows[] = $row;
                $count++;
            }
        }
        
        fclose($handle);
        
        \Log::info('Parsed CSV data', [
            'total_rows' => count($rows), 
            'headers_count' => count($headers),
            'headers' => $headers
        ]);
        
        return response()->json([
            'headers' => $headers,
            'rows' => $rows,
            'totalRows' => count($rows)
        ]);
        
    } else {
        return response()->json(['error' => 'Failed to open CSV file'], 500);
    }
}

// Helper method to parse Excel file
private function parseExcelFile($filePath)
{
    try {
        // Check if Excel package is installed
        if (!class_exists('Maatwebsite\Excel\Excel')) {
            return response()->json(['error' => 'Excel package not installed'], 500);
        }
        
        $data = \Excel::toArray([], $filePath);
        
        if (empty($data[0])) {
            return response()->json(['error' => 'Empty Excel file'], 400);
        }
        
        $excelData = $data[0];
        
        // Skip metadata rows (first 5 rows + empty row)
        array_splice($excelData, 0, 6);
        
        $headers = $excelData[0] ?? [];
        
        // Remove header row
        array_shift($excelData);
        
        $rows = [];
        foreach ($excelData as $row) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = $row[$index] ?? '';
            }
            $rows[] = $rowData;
        }
        
        return response()->json([
            'headers' => $headers,
            'rows' => $rows,
            'totalRows' => count($rows)
        ]);
        
    } catch (\Exception $e) {
        return response()->json(['error' => 'Failed to parse Excel file: ' . $e->getMessage()], 500);
    }
}
// create excel format file
public function createAppointmentsBackupExcel(Request $request)
{
  try {
        // Get the filtered appointments array from the frontend
        $appointmentsData = $request->input('appointments', []);
        $filters = $request->input('filters', []);
        
        if (empty($appointmentsData)) {
            return response()->json([
                'success' => false,
                'message' => 'No appointments to export.'
            ], 400);
        }

        // Convert the array of appointment data to objects
        $appointments = collect($appointmentsData);
        
        // Generate Excel content with filter info
        $filterSummary = $this->buildFilterSummary($filters);
        $excelContent = $this->generateExcelContentFromArray($appointments, $filterSummary);
        
        // Generate filename with timestamp
        $filename = $this->generateExcelFilenameFromFilters($filters);
        $filePath = "backups/{$filename}";
        
        // Ensure backups directory exists
        Storage::disk('local')->makeDirectory('backups');
        
        // Save Excel file to storage
        Storage::disk('local')->put($filePath, $excelContent);
        
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
            'message' => 'Excel backup created successfully.',
            'filename' => $filename,
            'file_path' => $filePath,
            'records_exported' => $appointments->count()
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Excel Backup creation failed: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to create Excel backup: ' . $e->getMessage()
        ], 500);
    }
}

// Generate Excel content (CSV format) from filtered array
private function generateExcelContentFromArray($appointments, $filterSummary)
{
    // Create CSV headers
    $csv = "LegalConnect - Appointments Backup\r\n";
    $csv .= "Filters: {$filterSummary}\r\n";
    $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\r\n";
    $csv .= "Total Records: " . $appointments->count() . "\r\n\r\n";
    
    // Column headers
    $headers = [
        'Full Name',
        'Email',
        'Phone',
        'Address',
        'Category',
        'Case Name',
        'Branch',
        'Selected Date',
        'Selected Time',
        'Status',
        'Terms Accepted',
        'Created At'
    ];
    
    $csv .= implode(',', array_map(function($h) { return '"' . str_replace('"', '""', $h) . '"'; }, $headers)) . "\r\n";
    
    // Add appointment data
    foreach ($appointments as $appointment) {
        $row = [
            $appointment['fullname'] ?? '',
            $appointment['email'] ?? '',
            $appointment['phone'] ?? '',
            $appointment['address'] ?? '',
            $appointment['category'] ?? '',
            $appointment['case_name'] ?? '',
            $appointment['selected_branch'] ?? '',
            $appointment['selected_date'] ?? '',
            $appointment['selected_time'] ?? '',
            $appointment['appointment_approval'] ?? '',
            $appointment['term_status'] ?? '',
            $appointment['created_at'] ?? ''
        ];
        
        $csv .= implode(',', array_map(function($v) { return '"' . str_replace('"', '""', $v) . '"'; }, $row)) . "\r\n";
    }
    
    return $csv;
}

// Helper method: Generate Excel filename from filters
private function generateExcelFilenameFromFilters($filters)
{
    $timestamp = now()->format('Y_m_d_His');
    $filterParts = [];
    
    if (!empty($filters['status']) && $filters['status'] !== 'all') {
        $filterParts[] = strtolower($filters['status']);
    }
    
    if (!empty($filters['category']) && $filters['category'] !== 'all') {
        $filterParts[] = 'cat_' . strtolower(str_replace(' ', '', $filters['category']));
    }
    
    $filterSuffix = !empty($filterParts) ? '_' . implode('_', $filterParts) : '';
    return "appointments{$filterSuffix}_{$timestamp}.csv";
}

// Generate Excel content (CSV format)
private function generateExcelContent($appointments, $filter, $branch = null)
{
    // Create CSV headers WITH Branch
    $csv = "LegalConnect - Appointments Backup\r\n";
    $csv .= "Filter: {$filter}\r\n";
    $csv .= "Branch: " . ($branch ?: 'All') . "\r\n"; // Add this line
    $csv .= "Generated: " . now()->format('Y-m-d H:i:s') . "\r\n";
    $csv .= "Total Records: " . $appointments->count() . "\r\n\r\n";
    
    // Column headers WITH Branch
    $headers = [
        'Full Name',
        'Email',
        'Phone',
        'Address',
        'Category',
        'Case Name',
        'Branch', // Add this header
        'Selected Date',
        'Selected Time',
        'Status',
        'Terms Accepted',
        'Created At',
        'Updated At'
    ];
    
    $csv .= implode(',', $headers) . "\r\n";
    
    // Data rows WITH Branch
   foreach ($appointments as $appointment) {
        $row = [
            '"' . str_replace('"', '""', $appointment->fullname ?? '') . '"',
            '"' . str_replace('"', '""', $appointment->email ?? '') . '"',
            '"' . str_replace('"', '""', $appointment->phone ?? '') . '"',
            '"' . str_replace('"', '""', $appointment->address ?? '') . '"',
            '"' . str_replace('"', '""', $appointment->category ?? '') . '"',
            '"' . str_replace('"', '""', $appointment->case_name ?? '') . '"',
            '"' . str_replace('"', '""', $appointment->selected_branch ?? '') . '"', // CHANGE THIS: $appointment->branch -> $appointment->selected_branch
            $appointment->selected_date ?? '',
            $appointment->selected_time ?? '',
            '"' . str_replace('"', '""', $appointment->appointment_approval ?? '') . '"',
            '"' . str_replace('"', '""', $appointment->term_status ?? '') . '"',
            $appointment->created_at ?? '',
            $appointment->updated_at ?? ''
        ];
        
        $csv .= implode(',', $row) . "\r\n";
    }
    
    return $csv;
}

// Generate Excel filename
private function generateExcelFilename($filter, $branch = null)
{
    $timestamp = now()->format('Y_m_d_His');
    $branchSuffix = $branch ? "_{$branch}" : '';
    return "{$filter}_appointments{$branchSuffix}_{$timestamp}.csv";
}


}