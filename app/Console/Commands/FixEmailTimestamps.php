<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FixEmailTimestamps extends Command
{
    protected $signature = 'email:fix-timestamps';
    protected $description = 'Fix email timestamp inconsistencies';

    public function handle()
    {
        $this->info('Fixing email timestamps...');

        // Fix sent messages (convert from UTC to Manila)
        $fixedSent = DB::table('chattbl')
            ->where(function($query) {
                $query->where('sender_email', 'cafirma.jerome07@gmail.com')
                      ->orWhere('sender_role', '!=', 'email');
            })
            ->update([
                'created_at' => DB::raw('DATE_ADD(created_at, INTERVAL 8 HOUR)'),
                'updated_at' => DB::raw('DATE_ADD(updated_at, INTERVAL 8 HOUR)')
            ]);

        $this->info("Fixed {$fixedSent} sent messages");

        // Verify current timezone settings
        $sampleMessages = DB::table('chattbl')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->info("Sample messages after fix:");
        foreach ($sampleMessages as $message) {
            $this->line("ID: {$message->id}, From: {$message->sender_email}, Time: {$message->created_at}");
        }

        $this->info('Timestamp fix completed!');
        
        return Command::SUCCESS;
    }
}