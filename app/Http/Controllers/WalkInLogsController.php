<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;  // <-- CORRECT: Namespace import at top
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WalkinsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Hash;

class WalkInLogsController extends Controller
{
    public function index()
    {
        try {
            // Get the authenticated user's office
            $user = auth()->user();
            $userOfficeId = $user->law_office_id ?? null;

            // Fetch walk-ins from database with office information, filtered by user's office
            $walkinsQuery = DB::table('diffun_walkins')
                ->leftJoin('law_offices', 'diffun_walkins.law_office_id', '=', 'law_offices.id')
                ->select('diffun_walkins.id', 'diffun_walkins.fullname', 'diffun_walkins.address', 'diffun_walkins.contact_number', 'diffun_walkins.purpose', 'diffun_walkins.branch', 'diffun_walkins.date_time', 'diffun_walkins.created_at', 'law_offices.law_office as office_name')
                ->whereNotNull('law_offices.id') // Only include records with valid office data
                ->orderBy('diffun_walkins.created_at', 'desc');

            // Filter by user's office if they have one assigned
            if ($userOfficeId) {
                $walkinsQuery->where('diffun_walkins.law_office_id', $userOfficeId);
            }

            $walkins = $walkinsQuery->get();
            
            // Fetch purposes from database for filter dropdown
            $purposes = DB::table('diffun_choice_purpose')
                ->select('id', 'purpose')
                ->orderBy('purpose', 'asc')
                ->get();
            
            // Fetch backup logs from database
            $backupLogs = DB::table('walkins_logs')
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
            
            return view('staff.walkInsLogs', compact('walkins', 'purposes', 'backupLogs'));
            
        } catch (\Exception $e) {
            Log::error('Error in WalkInLogsController: ' . $e->getMessage());
            
            $walkins = collect([]);
            $purposes = collect([]);
            $backupLogs = collect([]);
            return view('staff.walkInsLogs', compact('walkins', 'purposes', 'backupLogs'));
        }
    }

    /**
     * Resolve the full filesystem path for a stored backup entry.
     * Tries public storage path first (public/storage/walkin_logs_files/...),
     * then falls back to storage/app/public/walkin_logs_files/...
     */
    private function resolveBackupFullPath($dbPath)
    {
        // Normalize dbPath (may already contain folder/filename)
        $dbPath = ltrim($dbPath, '/\\');

        // public storage path
        $publicPath = public_path('storage/walkin_logs_files/' . $dbPath);
        if (file_exists($publicPath)) return $publicPath;

        // storage/app/public path
        $storagePath = storage_path('app/public/walkin_logs_files/' . $dbPath);
        if (file_exists($storagePath)) return $storagePath;

        return null;
    }

    /**
     * View backup file content
     */
    public function viewBackup($id)
    {
        try {
            // Check if user is authenticated and is staff
            if (!auth()->check() || !in_array(auth()->user()->role, ['staff', 'admin', 'superadmin', 'diffun_staff', 'cordon_staff'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 401);
            }
            
            // Get the backup record from database - ADD THIS LINE
            $backup = DB::table('walkins_logs')->where('id', $id)->first();
            
            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found.'
                ], 404);
            }
            
            // Decrypt the file name
            $decryptedFileName = Crypt::decryptString($backup->file_name);
            $fileExtension = strtolower(pathinfo($decryptedFileName, PATHINFO_EXTENSION));
            
            // Resolve full path to the file (prefer public storage path)
            $fullPath = $this->resolveBackupFullPath($backup->file_path);

            if (!$fullPath || !file_exists($fullPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found in storage.'
                ], 404);
            }
            
            // Prepare response data
            $response = [
                'success' => true,
                'filename' => $decryptedFileName,
                'date' => Carbon::parse($backup->created_at)->format('n/j/Y, g:i:s A'),
                'type' => $fileExtension
            ];
            
            // Handle different file types
            if ($fileExtension === 'pdf') {
                // For PDF files, encode content as base64
                $content = base64_encode(file_get_contents($fullPath));
                $response['content'] = $content;
                
            } elseif (in_array($fileExtension, ['xlsx', 'xls', 'csv'])) {
                // For Excel/CSV files, read and convert to array
                if ($fileExtension === 'csv') {
                    // Read CSV file
                    $csvData = [];
                    $csvString = ''; // Add this to store CSV as string
                    
                    if (($handle = fopen($fullPath, 'r')) !== false) {
                        // First, read as string for JavaScript parsing
                        $csvString = file_get_contents($fullPath);
                        
                        // Also read as array for structured data
                        while (($row = fgetcsv($handle)) !== false) {
                            $csvData[] = $row;
                        }
                        fclose($handle);
                    }
                    
                    // Return both string and array formats
                    $response['content'] = $csvString; // String for JS parsing
                    $response['data'] = $csvData; // Array for backup
                    $response['hasHeader'] = true;
                    
                } else {
                    // For Excel files, you might want to use a library like PhpSpreadsheet
                    // For now, return minimal info and let frontend handle download
                    $response['message'] = 'Excel files can be downloaded for viewing';
                    $response['url'] = url('/staff/walkins/logs/download-file/' . $id);
                }
            }
            
            return response()->json($response);
            
        } catch (\Exception $e) {
            Log::error('Error viewing backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete backup file and record
     */
    public function deleteBackup($id)
    {
        try {
            $backup = DB::table('walkins_logs')->where('id', $id)->first();
            
            if (!$backup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found.'
                ], 404);
            }
            
            // Decrypt the file name for logging
            $decryptedFileName = Crypt::decryptString($backup->file_name);
            
            // Full path to the file (resolve either public or storage path)
            $fullPath = $this->resolveBackupFullPath($backup->file_path);

            // Delete the physical file if it exists
            if ($fullPath && file_exists($fullPath)) {
                unlink($fullPath);
                Log::info('Deleted backup file: ' . $fullPath);
            }
            
            // Delete the record from database
            DB::table('walkins_logs')->where('id', $id)->delete();
            
            Log::info('Deleted backup record ID ' . $id . ' - ' . $decryptedFileName);
            
            return response()->json([
                'success' => true,
                'message' => 'Backup file deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting backup: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Read Excel file and convert to HTML table
     */
    private function readExcelFile($filePath)
    {
        try {
            // Load Excel file
            $excel = Excel::toArray([], $filePath);
            
            if (empty($excel)) {
                return '<p>No data found in Excel file.</p>';
            }
            
            // Get first sheet data
            $sheetData = $excel[0];
            
            // Convert to HTML table
            $html = '<div class="table-responsive">';
            $html .= '<table class="table table-bordered table-sm">';
            
            foreach ($sheetData as $rowIndex => $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    if ($rowIndex === 0) {
                        // Header row
                        $html .= '<th>' . htmlspecialchars($cell ?? '') . '</th>';
                    } else {
                        // Data row
                        $html .= '<td>' . htmlspecialchars($cell ?? '') . '</td>';
                    }
                }
                $html .= '</tr>';
            }
            
            $html .= '</table>';
            $html .= '</div>';
            
            return $html;
            
        } catch (\Exception $e) {
            Log::error('Error reading Excel file: ' . $e->getMessage());
            return '<p class="text-danger">Error reading Excel file: ' . $e->getMessage() . '</p>';
        }
    }

    /**
     * Download backup file directly
     */
    public function downloadBackupFile($id)
    {
        try {
            $backup = DB::table('walkins_logs')->where('id', $id)->first();
            
            if (!$backup) {
                return redirect()->back()->with('error', 'Backup file not found.');
            }
            
            // Decrypt the file name
            $decryptedFileName = Crypt::decryptString($backup->file_name);
            
            // Resolve full path and return for download
            $fullPath = $this->resolveBackupFullPath($backup->file_path);

            if (!$fullPath || !file_exists($fullPath)) {
                return redirect()->back()->with('error', 'File not found in storage.');
            }

            return response()->download($fullPath, $decryptedFileName);

        } catch (\Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to download file: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            // Get the authenticated user's office
            $user = auth()->user();
            $userOfficeId = $user->law_office_id ?? null;

            $query = DB::table('diffun_walkins')
                ->select('id', 'fullname', 'address', 'contact_number', 'purpose', 'branch', 'date_time', 'created_at')
                ->where('id', $id);

            // Filter by user's office if they have one assigned
            if ($userOfficeId) {
                $query->where('law_office_id', $userOfficeId);
            }

            $walkin = $query->first();

            if (!$walkin) {
                abort(404);
            }

            return view('diffun_staff.walkin-details', compact('walkin'));
            
        } catch (\Exception $e) {
            Log::error('Error in WalkInLogsController::show: ' . $e->getMessage());
            abort(404);
        }
    }

    /**
     * Export walk-ins as PDF and save to storage
     */
    public function exportPdf(Request $request)
    {
        try {
            // Get the authenticated user's office
            $user = auth()->user();
            $userOfficeId = $user->law_office_id ?? null;

            // Determine which walkins table to query based on branch
            $tableName = 'diffun_walkins';
            if ($request->has('branch') && !empty($request->branch)) {
                $b = strtolower($request->branch);
                if ($b === 'cordon') {
                    $tableName = 'cordon_walkins';
                }
                // add other branch mappings here if needed
            }

            // Build query with filters
            $query = DB::table($tableName)
                ->leftJoin('law_offices', $tableName . '.law_office_id', '=', 'law_offices.id')
                ->select($tableName . '.id', $tableName . '.fullname', $tableName . '.address', $tableName . '.contact_number', $tableName . '.purpose', $tableName . '.branch', $tableName . '.date_time', $tableName . '.created_at', 'law_offices.law_office as office_name')
                ->whereNotNull('law_offices.id'); // Only include records with valid office data
            
            // Filter by user's office if they have one assigned
            if ($userOfficeId) {
                $query->where($tableName . '.law_office_id', $userOfficeId);
            }
            
            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('fullname', 'like', '%' . $searchTerm . '%')
                    ->orWhere('address', 'like', '%' . $searchTerm . '%')
                    ->orWhere('contact_number', 'like', '%' . $searchTerm . '%')
                    ->orWhere('purpose', 'like', '%' . $searchTerm . '%')
                    ->orWhere('branch', 'like', '%' . $searchTerm . '%');
                });
            }
            
            // Apply purpose filter if provided
            if ($request->has('purpose') && !empty($request->purpose)) {
                $query->where('purpose', $request->purpose);
            }
            
            // Apply sorting if provided, otherwise use default
            if ($request->has('sort_column') && $request->has('sort_order')) {
                $sortColumn = $this->getSortColumn($request->sort_column);
                $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
                $query->orderBy($sortColumn, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $walkins = $query->get();
            
            if ($walkins->isEmpty()) {
                return redirect()->back()->with('error', 'No walk-in records found to export with the current filters.');
            }
            
            // Generate file name with filter info
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now()->format('His');
            $filterInfo = '';
            
            if ($request->has('search') && !empty($request->search)) {
                $filterInfo .= '_search-' . substr($request->search, 0, 20);
            }
            if ($request->has('purpose') && !empty($request->purpose)) {
                $filterInfo .= '_purpose-' . substr($request->purpose, 0, 20);
            }
            
            $fileName = "walkins_logs_filtered{$filterInfo}_{$currentDate}_{$currentTime}.pdf";
            
            // Define base path for storage
            // Decide folder based on branch (default diffun)
            $dbFolder = isset($request->branch) && !empty($request->branch) ? strtolower($request->branch) : 'diffun';
            $basePath = public_path('storage/walkin_logs_files/' . $dbFolder);
            
            // Create the directory if it doesn't exist
            if (!file_exists($basePath)) {
                mkdir($basePath, 0755, true);
            }
            
            // Full path for saving
            $fullPath = $basePath . '/' . $fileName;

            // Database path (relative path used in DB) e.g. cordon/filename.ext
            $dbPath = $dbFolder . '/' . $fileName;
            
            // Pass filter info to view for PDF header
            $filterDetails = [];
            if ($request->has('search') && !empty($request->search)) {
                $filterDetails['search'] = $request->search;
            }
            if ($request->has('purpose') && !empty($request->purpose)) {
                $filterDetails['purpose'] = $request->purpose;
            }
            
            // Generate PDF
            // Prefer using a Blade view named pdf.CordonWalkins for the Cordon branch. If absent, fall back to the PHP template file or default view.
            if (isset($dbFolder) && strtolower($dbFolder) === 'cordon') {
                if (View::exists('pdf.CordonWalkins')) {
                    $pdf = Pdf::loadView('pdf.CordonWalkins', compact('walkins', 'filterDetails'))
                        ->setPaper('a4', 'landscape')
                        ->setOptions([
                            'defaultFont' => 'sans-serif',
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true
                        ]);
                } else {
                    $viewPath = resource_path('views/pdf/CordonWalkins.php');
                    if (file_exists($viewPath)) {
                        $html = view()->file($viewPath, compact('walkins', 'filterDetails'))->render();
                        $pdf = Pdf::loadHTML($html)
                            ->setPaper('a4', 'landscape')
                            ->setOptions([
                                'defaultFont' => 'sans-serif',
                                'isHtml5ParserEnabled' => true,
                                'isRemoteEnabled' => true
                            ]);
                    } else {
                        Log::warning('CordonWalkins template not found. Falling back to pdf.walkins');
                        $pdf = Pdf::loadView('pdf.walkins', compact('walkins', 'filterDetails'))
                            ->setPaper('a4', 'landscape')
                            ->setOptions([
                                'defaultFont' => 'sans-serif',
                                'isHtml5ParserEnabled' => true,
                                'isRemoteEnabled' => true
                            ]);
                    }
                }
            } else {
                $pdf = Pdf::loadView('pdf.walkins', compact('walkins', 'filterDetails'))
                    ->setPaper('a4', 'landscape')
                    ->setOptions([
                        'defaultFont' => 'sans-serif',
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => true
                    ]);
            }
            
            // Save PDF to storage path
            $pdf->save($fullPath);
            
            // Ensure target directory exists
            if (!file_exists($basePath)) {
                mkdir($basePath, 0755, true);
            }

            // Encrypt the file name
            $encryptedFileName = Crypt::encryptString($fileName);

            // Avoid duplicate inserts for the same file_path
            if (!DB::table('walkins_logs')->where('file_path', $dbPath)->exists()) {
                DB::table('walkins_logs')->insert([
                    'file_name' => $encryptedFileName,
                    'file_path' => $dbPath,
                    'created_at' => now()
                ]);
            } else {
                Log::warning('Skipped inserting duplicate backup record for: ' . $dbPath);
            }
            
            Log::info('Filtered PDF exported and saved: ' . $fullPath . ' with filters: ' . json_encode($request->all()));
            
            // Also offer immediate download
            if ($request->has('download') && $request->download === 'true') {
                return response()->download($fullPath, $fileName);
            }
            
            return redirect()->back()->with('success', 'Filtered PDF exported and saved successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error exporting filtered PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export PDF: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to map DataTable column index to database column
     */
    private function getSortColumn($columnIndex)
    {
        $columns = [
            0 => 'fullname',     // FULL NAME
            1 => 'address',      // ADDRESS
            2 => 'contact_number',// CONTACT
            3 => 'purpose',      // PURPOSE
            4 => 'branch',       // BRANCH
            5 => 'date_time',    // DATE & TIME
            6 => 'created_at'    // CREATED
        ];
        
        return $columns[$columnIndex] ?? 'created_at';
    }

    /**
     * Delete a walk-in record
     */
    public function deleteWalkin($id)
    {
        try {
            // Get the authenticated user's office
            $user = auth()->user();
            $userOfficeId = $user->law_office_id ?? null;

            // Check if the walk-in exists and belongs to user's office
            $query = DB::table('diffun_walkins')->where('id', $id);
            
            // Filter by user's office if they have one assigned
            if ($userOfficeId) {
                $query->where('law_office_id', $userOfficeId);
            }
            
            $walkin = $query->first();
            
            if (!$walkin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Walk-in record not found or access denied.'
                ], 404);
            }
            
            // Get the walk-in name for logging
            $walkinName = $walkin->fullname;
            
            // Delete the walk-in record
            DB::table('diffun_walkins')->where('id', $id)->delete();
            
            Log::info('Deleted walk-in record ID ' . $id . ' - ' . $walkinName);
            
            return response()->json([
                'success' => true,
                'message' => 'Walk-in record deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error deleting walk-in: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete walk-in record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export walk-ins as CSV and save to storage
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get the authenticated user's office
            $user = auth()->user();
            $userOfficeId = $user->law_office_id ?? null;

            // Determine which walkins table to query based on branch
            $tableName = 'diffun_walkins';
            if ($request->has('branch') && !empty($request->branch)) {
                $b = strtolower($request->branch);
                if ($b === 'cordon') {
                    $tableName = 'cordon_walkins';
                }
                // add other branch mappings here if needed
            }

            // Build query with filters
            $query = DB::table($tableName)
                ->leftJoin('law_offices', $tableName . '.law_office_id', '=', 'law_offices.id')
                ->select($tableName . '.id', $tableName . '.fullname', $tableName . '.address', $tableName . '.contact_number', $tableName . '.purpose', $tableName . '.branch', $tableName . '.date_time', $tableName . '.created_at', 'law_offices.law_office as office_name')
                ->whereNotNull('law_offices.id'); // Only include records with valid office data
            
            // Filter by user's office if they have one assigned
            if ($userOfficeId) {
                $query->where($tableName . '.law_office_id', $userOfficeId);
            }
            
            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('fullname', 'like', '%' . $searchTerm . '%')
                    ->orWhere('address', 'like', '%' . $searchTerm . '%')
                    ->orWhere('contact_number', 'like', '%' . $searchTerm . '%')
                    ->orWhere('purpose', 'like', '%' . $searchTerm . '%')
                    ->orWhere('branch', 'like', '%' . $searchTerm . '%');
                });
            }
            
            // Apply purpose filter if provided
            if ($request->has('purpose') && !empty($request->purpose)) {
                $query->where('purpose', $request->purpose);
            }
            
            // Apply sorting if provided, otherwise use default
            if ($request->has('sort_column') && $request->has('sort_order')) {
                $sortColumn = $this->getSortColumn($request->sort_column);
                $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
                $query->orderBy($sortColumn, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            $walkins = $query->get();
            
            if ($walkins->isEmpty()) {
                return redirect()->back()->with('error', 'No walk-in records found to export with the current filters.');
            }
            
            // Generate file name with filter info
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentTime = Carbon::now()->format('His');
            $filterInfo = '';
            
            if ($request->has('search') && !empty($request->search)) {
                $filterInfo .= '_search-' . substr($request->search, 0, 20);
            }
            if ($request->has('purpose') && !empty($request->purpose)) {
                $filterInfo .= '_purpose-' . substr($request->purpose, 0, 20);
            }
            
            $fileName = "walkins_logs_filtered{$filterInfo}_{$currentDate}_{$currentTime}.csv";
            
            // Define base path for storage
            // Decide folder based on branch (default diffun)
            $dbFolder = isset($request->branch) && !empty($request->branch) ? strtolower($request->branch) : 'diffun';
            $basePath = public_path('storage/walkin_logs_files/' . $dbFolder);
            
            // Create the directory if it doesn't exist
            if (!file_exists($basePath)) {
                mkdir($basePath, 0755, true);
            }
            
            // Full path for saving
            $fullPath = $basePath . '/' . $fileName;

            // Database path (relative path used in DB)
            $dbPath = $dbFolder . '/' . $fileName;
            
            // Ensure target directory exists
            if (!file_exists($basePath)) {
                mkdir($basePath, 0755, true);
            }

            // Create CSV file
            $file = fopen($fullPath, 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fwrite($file, "\xEF\xBB\xBF");
            
            // Add CSV headers
            fputcsv($file, ['Full Name', 'Address', 'Contact', 'Purpose', 'Branch', 'Date & Time', 'Created At']);
            
            // Add data rows with cleaned data
            foreach ($walkins as $walkin) {
                // Clean the data
                $cleanAddress = trim(str_replace(['"', "'"], '', $walkin->address));
                $cleanPurpose = trim(str_replace(['"', "'"], '', $walkin->purpose));
                $cleanFullname = trim(str_replace(['"', "'"], '', $walkin->fullname));
                
                // Clean contact number
                $cleanContact = isset($walkin->contact_number) ? trim(str_replace(['"', "'"], '', $walkin->contact_number)) : '';

                fputcsv($file, [
                    $cleanFullname,
                    $cleanAddress,
                    $cleanContact,
                    $cleanPurpose,
                    $walkin->branch ?? 'Diffun Branch Office',
                    $walkin->date_time ? date('Y-m-d g:i A', strtotime($walkin->date_time)) : 'N/A',
                    date('Y-m-d', strtotime($walkin->created_at))
                ]);
            }
            
            fclose($file);
            
            // Encrypt the file name
            $encryptedFileName = Crypt::encryptString($fileName);

            // Avoid duplicate inserts for the same file_path
            if (!DB::table('walkins_logs')->where('file_path', $dbPath)->exists()) {
                DB::table('walkins_logs')->insert([
                    'file_name' => $encryptedFileName,
                    'file_path' => $dbPath,
                    'created_at' => now()
                ]);
            } else {
                Log::warning('Skipped inserting duplicate backup record for: ' . $dbPath);
            }
            
            Log::info('Filtered CSV exported and saved: ' . $fullPath . ' with filters: ' . json_encode($request->all()));
            
            // Also offer immediate download
            if ($request->has('download') && $request->download === 'true') {
                return response()->download($fullPath, $fileName);
            }
            
            return redirect()->back()->with('success', 'Filtered CSV file exported and saved successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error exporting filtered CSV: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export CSV: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup($id)
    {
        try {
            $backup = DB::table('walkins_logs')->where('id', $id)->first();
            
            if (!$backup) {
                return redirect()->back()->with('error', 'Backup file not found.');
            }
            
            // Decrypt the file name
            $decryptedFileName = Crypt::decryptString($backup->file_name);
            
            // Extract the filename from the database path (diffun/filename.ext)
            $filenameOnly = basename($backup->file_path);
            
            // Check if file exists in the correct location
            $fullPath = storage_path('app/public/walkin_logs_files/' . $backup->file_path);
            
            if (!file_exists($fullPath)) {
                Log::error('File not found at: ' . $fullPath);
                return redirect()->back()->with('error', 'File not found in storage.');
            }

            // Return the file for download
            return response()->download($fullPath, $decryptedFileName);

        } catch (\Exception $e) {
            Log::error('Error downloading backup: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to download file: ' . $e->getMessage());
        }
    }

    /**
     * Get all backup logs for modal
     */
    public function getBackups()
    {
        try {
            $backups = DB::table('walkins_logs')->orderBy('created_at', 'desc')->get();
            
            $formattedBackups = [];
            foreach ($backups as $backup) {
                try {
                    // Decrypt the file name
                    $decryptedFileName = Crypt::decryptString($backup->file_name);
                    
                    // Get file extension and type
                    $extension = pathinfo($decryptedFileName, PATHINFO_EXTENSION);
                    $type = strtoupper($extension);
                    
                    $formattedBackups[] = [
                        'filename' => $decryptedFileName,
                        'type' => $type,
                        'date' => Carbon::parse($backup->created_at)->format('n/j/Y, g:i:s A'),
                        'id' => $backup->id
                    ];
                } catch (\Exception $e) {
                    Log::error('Error decrypting filename for backup ID ' . $backup->id . ': ' . $e->getMessage());
                    continue;
                }
            }
            
            return $formattedBackups;

        } catch (\Exception $e) {
            Log::error('Error getting backups: ' . $e->getMessage());
            return [];
        }
    }


/**
 * Get logbook password for diffun branch (id=1 only)
 */
public function getLogbookPassword()
{
    try {
        // Get only the record with id=1 and branch=diffun
        $logbook = DB::table('logbook_login')
            ->where('id', 1)
            ->where('branch', 'diffun')
            ->first(['id', 'username', 'branch', 'created_at']);
            
        if (!$logbook) {
            return response()->json([
                'success' => false,
                'message' => 'Logbook record not found for diffun branch'
            ], 404);
        }
        
        $data = [
            'id' => $logbook->id,
            'username' => $logbook->username,
            'branch' => $logbook->branch,
            'created_at' => Carbon::parse($logbook->created_at)->format('n/j/Y g:i A'),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Logbook password loaded successfully'
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error getting logbook password: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to load logbook password: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Update logbook password (id=1, branch=diffun)
     */
    public function updateLogbookPassword(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'id' => 'required|integer|in:1', // Only allow id=1
                'username' => 'required|string|max:255',
                'password' => 'nullable|string|max:255', // Make password optional
            ]);
            
            // Verify the record exists and is for diffun branch
            $logbook = DB::table('logbook_login')
                ->where('id', $request->id)
                ->where('branch', 'diffun')
                ->first();
                
            if (!$logbook) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logbook record not found for diffun branch'
                ], 404);
            }
            
            // Prepare update data
            $updateData = [
                'username' => $request->username,
                'updated_at' => now(),
            ];
            
            // Only update password if provided (use bcrypt)
            if (!empty($request->password)) {
                $updateData['password'] = Hash::make($request->password);
            } else {
                // If password is empty, keep the current one
                // Frontend intentionally leaves password field empty
                $updateData['password'] = $logbook->password;
            }
            
            // Update the record
            $updated = DB::table('logbook_login')
                ->where('id', $request->id)
                ->where('branch', 'diffun')
                ->update($updateData);
                
            if ($updated) {
                Log::info('Updated logbook password for diffun branch', [
                    'id' => $request->id,
                    'username' => $request->username,
                    'password_updated' => !empty($request->password),
                    'updated_by' => auth()->id() ?? 'system'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Logbook credentials updated successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No changes were made to the logbook password'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error updating logbook password: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update logbook password: ' . $e->getMessage()
            ], 500);
        }
    }
}