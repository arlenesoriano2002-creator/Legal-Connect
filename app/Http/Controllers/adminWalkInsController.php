<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class adminWalkInsController extends Controller
{
    /**
     * Display a combined list of walk-ins from diffun_walkins and cordon_walkins.
     */
    public function index(Request $request)
    {
        // Fetch both tables
        $diffun = DB::table('diffun_walkins')
            ->select('id','fullname','address','contact_number','purpose','branch','date_time','created_at','updated_at')
            ->get()
            ->map(function ($item) {
                $item->source = 'diffun';
                return $item;
            });

        $cordon = DB::table('cordon_walkins')
            ->select('id','fullname','address','contact_number','purpose','branch','date_time','created_at','updated_at')
            ->get()
            ->map(function ($item) {
                $item->source = 'cordon';
                return $item;
            });

        // Merge collections and sort by date_time desc (fall back to created_at)
        $merged = $diffun->merge($cordon)->sortByDesc(function ($row) {
            return $row->date_time ? strtotime($row->date_time) : strtotime($row->created_at);
        })->values();

        return view('adminWalkIns', [
            'walkins' => $merged,
        ]);
    }

    /**
     * Delete a walk-in record from either diffun_walkins or cordon_walkins.
     */
    public function delete(Request $request, $id)
    {
        try {
            $source = $request->input('source', 'diffun');
            
            // Validate source
            if (!in_array($source, ['diffun', 'cordon'])) {
                $source = 'diffun'; // Default to diffun
            }

            $table = $source === 'cordon' ? 'cordon_walkins' : 'diffun_walkins';
            
            $deleted = DB::table($table)->where('id', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Walk-in record deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Record not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export combined walk-ins as CSV Excel file
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get combined walkins from both tables
            $diffun = DB::table('diffun_walkins')
                ->select('id','fullname','address','contact_number','purpose','branch','date_time','created_at')
                ->get()
                ->map(function ($item) {
                    $item->source = 'diffun';
                    return $item;
                });

            $cordon = DB::table('cordon_walkins')
                ->select('id','fullname','address','contact_number','purpose','branch','date_time','created_at')
                ->get()
                ->map(function ($item) {
                    $item->source = 'cordon';
                    return $item;
                });

            // Merge
            $walkins = $diffun->merge($cordon)->sortByDesc(function ($row) {
                return $row->date_time ? strtotime($row->date_time) : strtotime($row->created_at);
            })->values();

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $walkins = $walkins->filter(function($item) use ($searchTerm) {
                    return stripos($item->fullname, $searchTerm) !== false ||
                           stripos($item->address, $searchTerm) !== false ||
                           stripos($item->contact_number ?? '', $searchTerm) !== false ||
                           stripos($item->purpose, $searchTerm) !== false ||
                           stripos($item->branch ?? '', $searchTerm) !== false;
                });
            }

            // Apply branch filter if provided
            if ($request->has('branch') && !empty($request->branch)) {
                $branch = $request->branch;
                $walkins = $walkins->filter(function($item) use ($branch) {
                    return $item->branch === $branch;
                });
            }

            if ($walkins->isEmpty()) {
                return redirect()->back()->with('error', 'No walk-in records found to export.');
            }

            // Build descriptive filename based on filters
            $filterDescriptor = 'all';
            if ($request->has('search') && !empty($request->search)) {
                $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->search);
                $filterDescriptor = 'search_' . $sanitized;
            }
            if ($request->has('branch') && !empty($request->branch)) {
                $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->branch);
                $filterDescriptor = 'branch_' . $sanitized;
            }
            if ($request->has('search') && !empty($request->search) && $request->has('branch') && !empty($request->branch)) {
                $searchSanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->search);
                $branchSanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->branch);
                $filterDescriptor = 'filtered_search_' . $searchSanitized . '_branch_' . $branchSanitized;
            }

            // Generate file name with timestamp
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now()->format('His');
            $fileName = "admin_walkins_logs_{$filterDescriptor}_{$currentDate}_{$currentTime}.csv";
            
            // Define base path for storage
            $basePath = storage_path('app/public/walkin_logs_files/admin');
            
            // Create the directory if it doesn't exist
            if (!file_exists($basePath)) {
                mkdir($basePath, 0755, true);
            }
            
            // Full path for saving
            $fullPath = $basePath . '/' . $fileName;

            // Database path (relative path used in DB)
            $dbPath = 'admin/' . $fileName;
            
            // Create CSV file
            $file = fopen($fullPath, 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Add CSV headers
            fputcsv($file, ['Full Name', 'Address', 'Contact', 'Purpose', 'Branch', 'Date & Time', 'Created At', 'Source']);
            
            // Add data rows
            foreach ($walkins as $walkin) {
                fputcsv($file, [
                    $walkin->fullname ?? '',
                    $walkin->address ?? '',
                    $walkin->contact_number ?? '',
                    $walkin->purpose ?? '',
                    $walkin->branch ?? 'N/A',
                    $walkin->date_time ? date('Y-m-d g:i A', strtotime($walkin->date_time)) : 'N/A',
                    date('Y-m-d', strtotime($walkin->created_at)),
                    ucfirst($walkin->source ?? 'unknown')
                ]);
            }
            
            fclose($file);
            
            // Verify file was actually created
            if (!file_exists($fullPath)) {
                Log::error('CSV file was not created: ' . $fullPath);
                return redirect()->back()->with('error', 'Failed to create CSV file.');
            }
            
            // Encrypt the file name
            $encryptedFileName = Crypt::encryptString($fileName);

            // Store metadata in database with better error handling
            try {
                // Use insertOrIgnore to handle duplicate file_path (UNIQUE constraint)
                DB::table('walkins_logs')->insertOrIgnore([
                    'file_name' => $encryptedFileName,
                    'file_path' => $dbPath,
                    'created_at' => Carbon::now()
                ]);
                Log::info('Admin CSV exported and recorded: ' . $fullPath);
            } catch (\Exception $dbError) {
                Log::error('Database error recording CSV export: ' . $dbError->getMessage());
                Log::error('Stack: ' . $dbError->getTraceAsString());
                return response()->json(['success' => false, 'message' => 'File was created but database record failed.'], 500);
            }
            
            // Return success response (file saved to folder, not downloaded)
            return response()->json([
                'success' => true,
                'message' => 'Excel file saved successfully.',
                'fileName' => $fileName,
                'filePath' => $dbPath
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error exporting CSV: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to export CSV: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export combined walk-ins as PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Get combined walkins from both tables
            $diffun = DB::table('diffun_walkins')
                ->select('id','fullname','address','contact_number','purpose','branch','date_time','created_at')
                ->get()
                ->map(function ($item) {
                    $item->source = 'diffun';
                    return $item;
                });

            $cordon = DB::table('cordon_walkins')
                ->select('id','fullname','address','contact_number','purpose','branch','date_time','created_at')
                ->get()
                ->map(function ($item) {
                    $item->source = 'cordon';
                    return $item;
                });

            // Merge
            $walkins = $diffun->merge($cordon)->sortByDesc(function ($row) {
                return $row->date_time ? strtotime($row->date_time) : strtotime($row->created_at);
            })->values();

            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $walkins = $walkins->filter(function($item) use ($searchTerm) {
                    return stripos($item->fullname, $searchTerm) !== false ||
                           stripos($item->address, $searchTerm) !== false ||
                           stripos($item->contact_number ?? '', $searchTerm) !== false ||
                           stripos($item->purpose, $searchTerm) !== false ||
                           stripos($item->branch ?? '', $searchTerm) !== false;
                });
            }

            // Apply branch filter if provided
            if ($request->has('branch') && !empty($request->branch)) {
                $branch = $request->branch;
                $walkins = $walkins->filter(function($item) use ($branch) {
                    return $item->branch === $branch;
                });
            }

            if ($walkins->isEmpty()) {
                return redirect()->back()->with('error', 'No walk-in records found to export.');
            }

            // Build descriptive filename based on filters
            $filterDescriptor = 'all';
            if ($request->has('search') && !empty($request->search)) {
                $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->search);
                $filterDescriptor = 'search_' . $sanitized;
            }
            if ($request->has('branch') && !empty($request->branch)) {
                $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->branch);
                $filterDescriptor = 'branch_' . $sanitized;
            }
            if ($request->has('search') && !empty($request->search) && $request->has('branch') && !empty($request->branch)) {
                $searchSanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->search);
                $branchSanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->branch);
                $filterDescriptor = 'filtered_search_' . $searchSanitized . '_branch_' . $branchSanitized;
            }

            // Generate file name
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now()->format('His');
            $fileName = "admin_walkins_logs_{$filterDescriptor}_{$currentDate}_{$currentTime}.pdf";
            
            // Define base path for storage
            $basePath = storage_path('app/public/walkin_logs_files/admin');
            
            // Create the directory if it doesn't exist
            if (!file_exists($basePath)) {
                mkdir($basePath, 0755, true);
            }
            
            // Full path for saving
            $fullPath = $basePath . '/' . $fileName;

            // Database path (relative path used in DB)
            $dbPath = 'admin/' . $fileName;
            
            // Create PDF content
            $html = '<html><head><meta charset="UTF-8"><style>';
            $html .= 'body { font-family: Arial, sans-serif; margin: 20px; }';
            $html .= 'h1 { text-align: center; color: #333; margin-bottom: 30px; }';
            $html .= 'table { width: 100%; border-collapse: collapse; margin-top: 20px; }';
            $html .= 'th { background-color: #dc3545; color: white; padding: 12px; text-align: left; font-weight: bold; border: 1px solid #ddd; }';
            $html .= 'td { padding: 10px; border: 1px solid #ddd; }';
            $html .= 'tr:nth-child(even) { background-color: #f9f9f9; }';
            $html .= 'tr:hover { background-color: #f5f5f5; }';
            $html .= '.footer { margin-top: 30px; text-align: center; color: #666; font-size: 12px; }';
            $html .= '</style></head><body>';
            $html .= '<h1>Combined Walk-in Logs Report</h1>';
            $html .= '<p style="text-align: center; color: #666;">Generated on: ' . Carbon::now()->format('Y-m-d H:i:s') . '</p>';
            $html .= '<table>';
            $html .= '<thead><tr>';
            $html .= '<th>Full Name</th><th>Address</th><th>Contact</th><th>Purpose</th><th>Branch</th><th>Date & Time</th><th>Created</th><th>Source</th>';
            $html .= '</tr></thead><tbody>';
            
            foreach ($walkins as $walkin) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($walkin->fullname ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($walkin->address ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($walkin->contact_number ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($walkin->purpose ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($walkin->branch ?? 'N/A') . '</td>';
                $html .= '<td>' . ($walkin->date_time ? date('Y-m-d g:i A', strtotime($walkin->date_time)) : 'N/A') . '</td>';
                $html .= '<td>' . date('Y-m-d', strtotime($walkin->created_at)) . '</td>';
                $html .= '<td>' . ucfirst($walkin->source ?? 'unknown') . '</td>';
                $html .= '</tr>';
            }
            
            $html .= '</tbody></table>';
            $html .= '<div class="footer">';
            $html .= '<p>Total Records: ' . count($walkins) . '</p>';
            $html .= '<p>LegalConnect - Admin Walk-in Logs</p>';
            $html .= '</div>';
            $html .= '</body></html>';
            
            // Generate PDF
            $pdf = Pdf::loadHTML($html)
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'defaultFont' => 'sans-serif',
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true
                ]);
            
            // Save PDF to disk
            $pdf->save($fullPath);
            
            // Verify file was actually created
            if (!file_exists($fullPath)) {
                Log::error('PDF file was not created: ' . $fullPath);
                return redirect()->back()->with('error', 'Failed to create PDF file.');
            }
            
            // Encrypt the file name
            $encryptedFileName = Crypt::encryptString($fileName);

            // Store metadata in database with better error handling
            try {
                // Use insertOrIgnore to handle duplicate file_path (UNIQUE constraint)
                DB::table('walkins_logs')->insertOrIgnore([
                    'file_name' => $encryptedFileName,
                    'file_path' => $dbPath,
                    'created_at' => Carbon::now()
                ]);
                Log::info('Admin PDF exported and recorded: ' . $fullPath);
            } catch (\Exception $dbError) {
                Log::error('Database error recording PDF export: ' . $dbError->getMessage());
                Log::error('Stack: ' . $dbError->getTraceAsString());
                return response()->json(['success' => false, 'message' => 'File was created but database record failed.'], 500);
            }
            
            // Return success response (file saved to folder, not downloaded)
            return response()->json([
                'success' => true,
                'message' => 'PDF file saved successfully.',
                'fileName' => $fileName,
                'filePath' => $dbPath
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Error exporting PDF: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to export PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get backup logs for display in modal
     */
    public function getBackupLogs()
    {
        try {
            $backupLogs = DB::table('walkins_logs')
                ->where('file_path', 'like', 'admin/%')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($log) {
                    try {
                        $log->decrypted_name = Crypt::decryptString($log->file_name);
                        $log->file_type = pathinfo($log->decrypted_name, PATHINFO_EXTENSION);
                        $log->formatted_date = Carbon::parse($log->created_at)->format('n/j/Y, g:i:s A');
                        return $log;
                    } catch (\Exception $e) {
                        $log->decrypted_name = 'Error decrypting';
                        $log->file_type = 'Unknown';
                        $log->formatted_date = Carbon::parse($log->created_at)->format('n/j/Y, g:i:s A');
                        return $log;
                    }
                });
            
            return response()->json([
                'success' => true,
                'backupLogs' => $backupLogs
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching backup logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching backup logs',
                'backupLogs' => []
            ], 500);
        }
    }

    /**
     * View/Preview backup file details
     */
    public function viewBackup(Request $request, $id)
    {
        try {
            $backup = DB::table('walkins_logs')
                ->where('id', $id)
                ->where('file_path', 'LIKE', 'admin/%')
                ->first();
            
            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }
            
            $decryptedName = Crypt::decryptString($backup->file_name);
            $filePath = storage_path('app/public/walkin_logs_files/' . $backup->file_path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on disk'
                ], 404);
            }
            
            $fileSize = filesize($filePath);
            $fileExtension = strtolower(pathinfo($decryptedName, PATHINFO_EXTENSION));
            
            $response = [
                'success' => true,
                'filename' => $decryptedName,
                'date' => Carbon::parse($backup->created_at)->format('n/j/Y, g:i:s A'),
                'type' => strtoupper($fileExtension),
                'file_size' => $this->formatBytes($fileSize),
                'id' => $id
            ];
            
            // Read file content based on file type
            if ($fileExtension === 'pdf') {
                $response['content'] = base64_encode(file_get_contents($filePath));
            } elseif (in_array($fileExtension, ['xlsx', 'xls'])) {
                // For Excel files, parse and return as array
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $data = [];
                    
                    foreach ($worksheet->getIterator() as $row) {
                        $rowData = [];
                        foreach ($row as $cell) {
                            $rowData[] = $cell->getValue();
                        }
                        if (!empty(array_filter($rowData))) {
                            $data[] = $rowData;
                        }
                    }
                    
                    $response['content'] = $data;
                    $response['hasHeader'] = true;
                } catch (\Exception $e) {
                    Log::error('Error reading Excel file: ' . $e->getMessage());
                    // Return empty content but don't fail
                    $response['content'] = [];
                }
            } elseif ($fileExtension === 'csv') {
                // For CSV files, read as string
                $response['content'] = file_get_contents($filePath);
            }
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Error viewing backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading backup file'
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackupFile($id)
    {
        try {
            $backup = DB::table('walkins_logs')
                ->where('id', $id)
                ->where('file_path', 'LIKE', 'admin/%')
                ->first();
            
            if (!$backup) {
                return redirect()->back()->with('error', 'Backup file not found');
            }
            
            $decryptedName = Crypt::decryptString($backup->file_name);
            $filePath = storage_path('app/public/walkin_logs_files/' . $backup->file_path);
            
            if (!file_exists($filePath)) {
                return redirect()->back()->with('error', 'File not found on disk');
            }
            
            Log::info('Admin backup file downloaded: ' . $filePath);
            return response()->download($filePath, $decryptedName);
        } catch (\Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error downloading file');
        }
    }

    /**
     * Delete backup file and its database record
     */
    public function deleteBackup(Request $request, $id)
    {
        try {
            $backup = DB::table('walkins_logs')
                ->where('id', $id)
                ->where('file_path', 'LIKE', 'admin/%')
                ->first();
            
            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ], 404);
            }
            
            $filePath = storage_path('app/public/walkin_logs_files/' . $backup->file_path);
            
            // Delete file from disk if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Delete database record
            DB::table('walkins_logs')->where('id', $id)->delete();
            
            Log::info('Admin backup file deleted: ' . $filePath);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup file deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting backup file'
            ], 500);
        }
    }

    /**
     * Helper function to format bytes
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
