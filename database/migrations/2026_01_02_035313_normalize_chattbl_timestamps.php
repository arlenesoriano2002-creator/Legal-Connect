<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up()
    {
        // Add normalized timestamp column
        Schema::table('chattbl', function (Blueprint $table) {
            $table->datetime('timestamp_normalized')->nullable()->after('updated_at');
        });
        
        // Give a moment for the column to be added
        sleep(1);
        
        // Normalize existing data in batches to avoid memory issues
        $this->normalizeTimestampsInBatches();
        
        // Make the column not nullable after populating
        Schema::table('chattbl', function (Blueprint $table) {
            $table->datetime('timestamp_normalized')->nullable(false)->change();
        });
        
        // Add index for better performance
        Schema::table('chattbl', function (Blueprint $table) {
            $table->index('timestamp_normalized');
        });
        
        // Optionally, you can also fix the original created_at column
        // $this->fixOriginalCreatedAtColumn();
    }
    
    private function normalizeTimestampsInBatches($batchSize = 100)
    {
        $totalRecords = DB::table('chattbl')->count();
        $processed = 0;
        
        while ($processed < $totalRecords) {
            $messages = DB::table('chattbl')
                ->select('id', 'created_at', 'updated_at')
                ->skip($processed)
                ->take($batchSize)
                ->get();
            
            foreach ($messages as $message) {
                $normalizedTime = $this->normalizeTimestamp($message->created_at, $message->updated_at);
                
                DB::table('chattbl')
                    ->where('id', $message->id)
                    ->update(['timestamp_normalized' => $normalizedTime]);
            }
            
            $processed += $batchSize;
            
            // Sleep briefly to avoid overwhelming the database
            usleep(100000); // 0.1 second
        }
    }
    
    private function normalizeTimestamp($createdAt, $updatedAt)
    {
        // Handle different timestamp formats
        if (empty($createdAt)) {
            // If created_at is empty, use updated_at
            return $this->parseAnyTimestamp($updatedAt);
        }
        
        return $this->parseAnyTimestamp($createdAt);
    }
    
    private function parseAnyTimestamp($timestamp)
    {
        if (empty($timestamp)) {
            return Carbon::now()->toDateTimeString();
        }
        
        // Remove any whitespace
        $timestamp = trim($timestamp);
        
        // Check for different patterns
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $timestamp)) {
            // Already in correct format: 'Y-m-d H:i:s'
            return $timestamp;
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $timestamp)) {
            // Date only: 'Y-m-d'
            return $timestamp . ' 00:00:00';
        } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $timestamp)) {
            // Time only: 'H:i:s'
            // Try to get date from updated_at or use today
            $datePart = date('Y-m-d'); // Default to today
            
            // You could also try to infer from context or use a default
            // For time-only entries without date, we'll use 1970-01-01
            // and mark them for manual review
            return '1970-01-01 ' . $timestamp;
        } elseif (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}', $timestamp)) {
            // ISO format: 'Y-m-dTH:i:s'
            return str_replace('T', ' ', substr($timestamp, 0, 19));
        }
        
        // Default: try Carbon's parse
        try {
            return Carbon::parse($timestamp)->toDateTimeString();
        } catch (\Exception $e) {
            // If all else fails, use current time
            return Carbon::now()->toDateTimeString();
        }
    }
    
    // Optional: Fix the original created_at column too
    private function fixOriginalCreatedAtColumn()
    {
        // Copy normalized timestamps back to created_at
        DB::statement("
            UPDATE chattbl 
            SET created_at = timestamp_normalized 
            WHERE created_at IS NULL 
               OR LENGTH(created_at) < 19 
               OR created_at NOT LIKE '____-__-__ __:__:__'
        ");
    }
    
    public function down()
    {
        Schema::table('chattbl', function (Blueprint $table) {
            $table->dropColumn('timestamp_normalized');
            $table->dropIndex('chattbl_timestamp_normalized_index');
        });
    }
};