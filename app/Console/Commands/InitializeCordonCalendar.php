<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InitializeCordonCalendar extends Command
{
    // The command signature (what you type to run it)
    protected $signature = 'cordon:init 
                            {--months=3 : Number of months to initialize} 
                            {--start=today : Start date (today or YYYY-MM-DD)}';
    
    // Command description
    protected $description = 'Initialize Cordon branch calendar database';

    public function handle()
    {
        $this->info('🚀 Initializing Cordon branch calendar...');
        
        // Get options
        $months = (int)$this->option('months');
        $startInput = $this->option('start');
        
        // Set start date
        if ($startInput === 'today') {
            $startDate = Carbon::now()->startOfMonth();
        } else {
            $startDate = Carbon::parse($startInput)->startOfMonth();
        }
        
        $endDate = $startDate->copy()->addMonths($months)->endOfMonth();
        
        $this->info("📅 Creating calendar from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
        $this->info("📊 Total months: {$months}");
        
        $current = $startDate->copy();
        $datesCreated = 0;
        $slotsCreated = 0;
        
        // Progress bar
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $bar = $this->output->createProgressBar($totalDays);
        
        while ($current <= $endDate) {
            $dateStr = $current->format('Y-m-d');
            
            // Skip weekends (optional - remove if you want all days)
            $dayOfWeek = $current->dayOfWeek;
            $isWeekend = ($dayOfWeek === 0 || $dayOfWeek === 6); // 0=Sunday, 6=Saturday
            
            if (!$isWeekend) {
                // Create date availability if it doesn't exist
                $dateExists = DB::table('cordon_date_availabilities')
                    ->where('date', $dateStr)
                    ->exists();
                    
                if (!$dateExists) {
                    DB::table('cordon_date_availabilities')->insert([
                        'date' => $dateStr,
                        'availability_status' => 'available',
                        'total_slots' => 9,
                        'booked_slots' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $datesCreated++;
                }
                
                // Create time slots if they don't exist
                $slotCount = DB::table('cordon_time_slots')
                    ->where('date', $dateStr)
                    ->count();
                    
                if ($slotCount === 0) {
                    $slots = [];
                    for ($hour = 8; $hour < 17; $hour++) {
                        $slotNumber = $hour - 7;
                        $slots[] = [
                            'date' => $dateStr,
                            'slot_number' => $slotNumber,
                            'start_time' => sprintf('%02d:00:00', $hour),
                            'end_time' => sprintf('%02d:00:00', $hour + 1),
                            'status' => 'available',
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }
                    
                    DB::table('cordon_time_slots')->insert($slots);
                    $slotsCreated += count($slots);
                }
            }
            
            $bar->advance();
            $current->addDay();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("✅ Cordon calendar initialized successfully!");
        $this->info("📅 Dates created: {$datesCreated}");
        $this->info("⏰ Time slots created: {$slotsCreated}");
        $this->info("📊 Total days processed: {$totalDays}");
        
        if ($isWeekend) {
            $this->warn("Note: Weekends were skipped. Run with --include-weekends to include them.");
        }
        
        Log::info('Cordon calendar initialized', [
            'dates_created' => $datesCreated,
            'slots_created' => $slotsCreated,
            'date_range' => $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d')
        ]);
        
        $this->info("\n🔧 Next steps:");
        $this->line("1. Refresh your browser (Ctrl+F5)");
        $this->line("2. Go to Calendar > Cordon Branch");
        $this->line("3. You should see the calendar with no errors");
        
        return Command::SUCCESS;
    }
}