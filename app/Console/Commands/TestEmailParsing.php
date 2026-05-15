<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class TestEmailParsing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email-dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email date parsing functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing email date parsing...');
        $this->line('Current Manila time: ' . Carbon::now('Asia/Manila')->toDateTimeString());
        
        // Test different date formats
        $testDates = [
            "Fri, 2 Jan 2026 11:22:00 +0000",
            "Fri, 2 Jan 2026 11:22:00 UTC",
            "Fri, 2 Jan 2026 11:22:00 GMT",
            "Fri, 2 Jan 2026 11:22:00",
            "Jan 2, 2026 11:22:00 AM",
            "2026-01-02 11:22:00",
            "02 Jan 2026 11:22:00 +0800",
            "Jan. 2 2026 7:22 pm", // Your problematic date format
        ];

        foreach ($testDates as $testDate) {
            $this->line("\nTesting: " . $testDate);
            
            try {
                // Method 1: Carbon parse
                $carbon = Carbon::parse($testDate);
                $this->info("Carbon::parse: " . $carbon->toDateTimeString() . " (" . $carbon->getTimezone()->getName() . ")");
                
                // Method 2: Carbon parse with UTC
                $carbonUTC = Carbon::parse($testDate . (strpos($testDate, '+') === false && strpos($testDate, 'UTC') === false && strpos($testDate, 'GMT') === false ? ' UTC' : ''));
                $this->info("Carbon::parse as UTC: " . $carbonUTC->toDateTimeString() . " (" . $carbonUTC->getTimezone()->getName() . ")");
                
                // Method 3: Convert to Manila
                $manila = $carbonUTC->setTimezone('Asia/Manila');
                $this->info("Manila time: " . $manila->toDateTimeString() . " (" . $manila->getTimezone()->getName() . ")");
                
                // Method 4: strtotime
                $timestamp = strtotime($testDate);
                $this->comment("strtotime timestamp: " . $timestamp);
                $this->comment("strtotime result: " . date('Y-m-d H:i:s', $timestamp));
                
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
            }
        }
        
        $this->info("\nTest completed!");
    }
}